<?php

namespace LucaCermenati\CommonTraits\Traits;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use LucaCermenati\CommonTraits\Contracts\FilterInterface;

trait Filterable
{
    /** Scope a query by delegating to the given filter for the given request. */
    public function scopeFilter(Builder $query, FilterInterface $filter, Request $request): Builder
    {
        return $filter->apply($query, $request);
    }
}
