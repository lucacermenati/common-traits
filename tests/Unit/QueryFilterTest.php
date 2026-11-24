<?php

declare(strict_types=1);

namespace LucaCermenati\CommonTraits\Tests\Unit;

use Illuminate\Http\Request;
use LucaCermenati\CommonTraits\Tests\Fixtures\User;
use LucaCermenati\CommonTraits\Tests\Fixtures\UserFilter;
use LucaCermenati\CommonTraits\Tests\TestCase;

class QueryFilterTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        User::create(['name' => 'Luca', 'created_at' => '2026-01-01 00:00:00']);
        User::create(['name' => 'Lisa', 'created_at' => '2026-03-01 00:00:00']);
    }

    /** Names of the users returned when the given query string is filtered. */
    private function filteredNames(array $input): array
    {
        $request = Request::create('/', 'GET', $input);

        return User::query()->filter(new UserFilter, $request)->pluck('name')->all();
    }

    public function test_snake_case_input_calls_camel_case_filter_method(): void
    {
        $this->assertSame(['Lisa'], $this->filteredNames(['created_from' => '2026-02-01']));
    }

    public function test_multiple_filters_are_combined(): void
    {
        $this->assertSame([], $this->filteredNames(['name' => 'Luca', 'created_from' => '2026-02-01']));
        $this->assertSame(['Luca'], $this->filteredNames(['name' => 'Luca', 'created_from' => '2025-12-01']));
    }

    public function test_empty_input_is_ignored(): void
    {
        $this->assertSame(['Luca', 'Lisa'], $this->filteredNames(['name' => '']));
    }

    public function test_unknown_input_is_ignored(): void
    {
        $this->assertSame(['Luca', 'Lisa'], $this->filteredNames(['unknown' => 'x']));
    }

    public function test_base_class_methods_cannot_be_called_from_the_request(): void
    {
        $this->assertSame(['Luca', 'Lisa'], $this->filteredNames(['apply' => 'x', 'non_empty_values' => 'y']));
    }
}
