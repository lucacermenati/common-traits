<?php

namespace LucaCermenati\CommonTraits\Traits;

use Illuminate\Database\Eloquent\Builder;

trait Searchable
{
    abstract protected function searchables(): array;

    /** Scope a query to search for a term in the model's searchable attributes. */
    public function scopeSearch(Builder $query, ?string $searchTerm = null, array $searchIn = []): Builder
    {
        $searchIn = $searchIn ?: $this->searchables();

        if (empty($searchIn) || empty($searchTerm)) {
            return $query;
        }

        $pattern = "%{$searchTerm}%";

        return $query->where(function (Builder $query) use ($searchIn, $pattern) {
            foreach ($searchIn as $field) {
                // A dotted field is a relationship path ending in the column to search
                if (str_contains($field, '.')) {
                    $parts = explode('.', $field);
                    $column = array_pop($parts);
                    $relationPath = implode('.', $parts);

                    $query->orWhereHas($relationPath, function (Builder $nestedQuery) use ($column, $pattern) {
                        $nestedQuery->whereLike($column, $pattern, caseSensitive: false);
                    });
                } else {
                    // Qualify the column with the table name to avoid conflicts with joined tables
                    $query->orWhereLike($query->qualifyColumn($field), $pattern, caseSensitive: false);
                }
            }
        });
    }
}
