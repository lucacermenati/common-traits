<?php

namespace LucaCermenati\CommonTraits\Traits;

use Illuminate\Database\Eloquent\Builder;
use LucaCermenati\CommonTraits\Contracts\SorterInterface;

trait Sortable
{
    public function scopeSortBy(Builder $query, string $sortKey, string $sortDirection, SorterInterface $sorter)
    {
        return $sorter->sort($query, $sortKey, $sortDirection);
    }
}