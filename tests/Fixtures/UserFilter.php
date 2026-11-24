<?php

declare(strict_types=1);

namespace LucaCermenati\CommonTraits\Tests\Fixtures;

use LucaCermenati\CommonTraits\Filters\QueryFilter;

class UserFilter extends QueryFilter
{
    public function name(string $value): void
    {
        $this->builder->where('name', $value);
    }

    public function createdFrom(string $value): void
    {
        $this->builder->where('created_at', '>=', $value);
    }
}
