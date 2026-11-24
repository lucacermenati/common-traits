<?php

declare(strict_types=1);

namespace LucaCermenati\CommonTraits\Tests\Unit;

use Illuminate\Http\Request;
use LucaCermenati\CommonTraits\Tests\Fixtures\User;
use LucaCermenati\CommonTraits\Tests\Fixtures\UserSorter;
use LucaCermenati\CommonTraits\Tests\TestCase;

class QuerySorterTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        User::create(['name' => 'Mario', 'last_name' => 'Rossi', 'created_at' => '2026-01-01 00:00:00']);
        User::create(['name' => 'Lisa', 'last_name' => 'Bianchi', 'created_at' => '2026-02-01 00:00:00']);
        User::create(['name' => 'Luca', 'last_name' => 'Verdi', 'created_at' => '2026-03-01 00:00:00']);
    }

    /** Last names of the users in the order returned when sorted by the given query string. */
    private function sortedLastNames(array $input): array
    {
        $request = Request::create('/', 'GET', $input);

        return User::query()->sort(new UserSorter, $request)->pluck('last_name')->all();
    }

    public function test_default_order_is_newest_first(): void
    {
        $this->assertSame(['Verdi', 'Bianchi', 'Rossi'], $this->sortedLastNames([]));
    }

    public function test_sort_input_orders_ascending(): void
    {
        $this->assertSame(['Bianchi', 'Rossi', 'Verdi'], $this->sortedLastNames(['sort' => 'last_name']));
    }

    public function test_leading_minus_orders_descending(): void
    {
        $this->assertSame(['Verdi', 'Rossi', 'Bianchi'], $this->sortedLastNames(['sort' => '-last_name']));
    }

    public function test_sort_method_without_direction_parameter_still_works(): void
    {
        // name() ignores the direction, so the minus has no effect
        $this->assertSame(['Bianchi', 'Verdi', 'Rossi'], $this->sortedLastNames(['sort' => '-name']));
    }

    public function test_unknown_sort_falls_back_to_default_order(): void
    {
        $this->assertSame(['Verdi', 'Bianchi', 'Rossi'], $this->sortedLastNames(['sort' => 'unknown']));
    }

    public function test_base_class_methods_cannot_be_called_from_the_request(): void
    {
        $this->assertSame(['Verdi', 'Bianchi', 'Rossi'], $this->sortedLastNames(['sort' => 'apply']));
    }

    public function test_default_order_breaks_ties_newest_first(): void
    {
        User::create(['name' => 'Paolo', 'last_name' => 'Rossi', 'created_at' => '2026-04-01 00:00:00']);

        $request = Request::create('/', 'GET', ['sort' => 'last_name']);
        $names = User::query()->sort(new UserSorter, $request)->pluck('name')->all();

        $this->assertSame(['Lisa', 'Paolo', 'Mario', 'Luca'], $names);
    }

    public function test_default_column_is_qualified_so_joins_are_not_ambiguous(): void
    {
        User::where('name', 'Lisa')->first()->posts()->create(['title' => 'Hello']);

        $request = Request::create('/', 'GET', []);
        $names = User::query()
            ->join('posts', 'posts.user_id', '=', 'users.id')
            ->sort(new UserSorter, $request)
            ->pluck('users.name')
            ->all();

        $this->assertSame(['Lisa'], $names);
    }
}
