<?php

namespace LucaCermenati\CommonTraits\Traits;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use LucaCermenati\CommonTraits\Contracts\SorterInterface;

trait Sortable
{
    /** Scope a query by delegating to the given sorter for the given request. */
    public function scopeSort(Builder $query, SorterInterface $sorter, Request $request): Builder
    {
        return $sorter->apply($query, $request);
    }
}
