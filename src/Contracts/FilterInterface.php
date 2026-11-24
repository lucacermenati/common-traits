<?php

namespace LucaCermenati\CommonTraits\Contracts;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

interface FilterInterface
{
    public function apply(Builder $query, Request $request): Builder;
}
