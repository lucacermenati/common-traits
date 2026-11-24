<?php

declare(strict_types=1);

namespace LucaCermenati\CommonTraits\Tests\Unit;

use LucaCermenati\CommonTraits\Tests\Fixtures\User;
use LucaCermenati\CommonTraits\Tests\TestCase;

class SearchableTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        User::create(['name' => 'Luca', 'email' => 'luca@example.com']);
        $lisa = User::create(['name' => 'Lisa', 'email' => 'lisa@example.com']);
        $lisa->posts()->create(['title' => 'Laravel tips']);
    }

    /** Names of the users found for the given search term. */
    private function foundNames(?string $term, array $searchIn = []): array
    {
        return User::query()->search($term, $searchIn)->pluck('name')->all();
    }

    public function test_matches_part_of_a_column(): void
    {
        $this->assertSame(['Luca'], $this->foundNames('luc'));
    }

    public function test_search_is_case_insensitive(): void
    {
        $this->assertSame(['Luca'], $this->foundNames('LUCA'));
    }

    public function test_searches_through_relationships(): void
    {
        $this->assertSame(['Lisa'], $this->foundNames('laravel'));
    }

    public function test_matches_any_searchable_column(): void
    {
        $this->assertSame(['Luca', 'Lisa'], $this->foundNames('example'));
    }

    public function test_explicit_columns_override_searchables(): void
    {
        $this->assertSame([], $this->foundNames('example', ['name']));
        $this->assertSame(['Luca'], $this->foundNames('luca', ['name']));
    }

    public function test_empty_term_returns_everything(): void
    {
        $this->assertSame(['Luca', 'Lisa'], $this->foundNames(''));
        $this->assertSame(['Luca', 'Lisa'], $this->foundNames(null));
    }
}
