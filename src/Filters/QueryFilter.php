<?php

namespace LucaCermenati\CommonTraits\Filters;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use LucaCermenati\CommonTraits\Contracts\FilterInterface;

/**
 * Base filter that maps request inputs to filter methods by name.
 *
 * Each request input is converted to camelCase and, if the concrete filter
 * declares a method with that name, the method is called with the input value.
 * For example `?created_from=2026-01-01` calls `createdFrom('2026-01-01')`.
 */
abstract class QueryFilter implements FilterInterface
{
    protected Builder $builder;

    public function apply(Builder $query, Request $request): Builder
    {
        $this->builder = $query;
        $filterMethods = $this->filterMethods();

        foreach ($this->nonEmptyValues($request) as $key => $value) {
            $method = Str::camel((string) $key);

            if (in_array($method, $filterMethods, true)) {
                $this->{$method}($value);
            }
        }

        return $this->builder;
    }

    protected function nonEmptyValues(Request $request): array
    {
        return collect($request->all())
            ->filter(fn ($value, $key) => $request->filled($key))
            ->all();
    }

    /**
     * Public and protected methods declared on the concrete filter.
     * These are the only methods reachable from the request.
     */
    private function filterMethods(): array
    {
        return array_diff(get_class_methods($this), get_class_methods(self::class));
    }
}
