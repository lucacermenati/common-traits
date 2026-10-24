<?php

namespace LucaCermenati\CommonTraits\Sorters;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use LucaCermenati\CommonTraits\Contracts\SorterInterface;

/**
 * Base sorter that maps the sort request input to a sort method by name.
 *
 * The value of the sort input is converted to camelCase and, if the concrete
 * sorter declares a method with that name, the method is called with the sort
 * direction. A leading minus flips the direction to descending. For example
 * `?sort=last_name` calls `lastName('asc')` and `?sort=-last_name` calls
 * `lastName('desc')`. The default order is always appended last as a tiebreaker.
 */
abstract class QuerySorter implements SorterInterface
{
    protected Builder $builder;

    protected string $sortInput = 'sort';

    protected string $defaultColumn = 'created_at';

    protected string $defaultDirection = 'desc';

    public function apply(Builder $query, Request $request): Builder
    {
        $this->builder = $query;

        if ($request->filled($this->sortInput)) {
            $raw = trim((string) $request->input($this->sortInput));
            $direction = str_starts_with($raw, '-') ? 'desc' : 'asc';
            $method = Str::camel(ltrim($raw, '-'));

            if (in_array($method, $this->sortMethods(), true)) {
                $this->{$method}($direction);
            }
        }

        return $this->builder->orderBy(
            $this->builder->qualifyColumn($this->defaultColumn),
            $this->defaultDirection,
        );
    }

    /**
     * Public and protected methods declared on the concrete sorter.
     * These are the only methods reachable from the request.
     */
    private function sortMethods(): array
    {
        return array_diff(get_class_methods($this), get_class_methods(self::class));
    }
}
