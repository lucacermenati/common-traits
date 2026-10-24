<?php

declare(strict_types=1);

namespace LucaCermenati\CommonTraits\Tests\Fixtures;

use LucaCermenati\CommonTraits\Sorters\QuerySorter;

class UserSorter extends QuerySorter
{
    public function lastName(string $direction): void
    {
        $this->builder->orderBy('last_name', $direction);
    }

    /** Declared without a parameter to prove pre-direction sorters keep working. */
    public function name(): void
    {
        $this->builder->orderBy('name');
    }
}
