<?php

namespace LucaCermenati\CommonTraits\Contracts;

use Illuminate\Database\Eloquent\Builder;

interface SorterInterface
{
    public function sort(Builder $query, string $sortKey, string $sortDirection);
}