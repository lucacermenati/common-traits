<?php

namespace LucaCermenati\CommonTraits\Traits;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use LucaCermenati\CommonTraits\Contracts\FilterInterface;

trait Filterable
{
    public function apply(Builder $query, FilterInterface $filter, Request $request)
    {
        return $filter->apply($query, $request);
    }
}