<?php

namespace LucaCermenati\CommonTraits\Traits;

use Illuminate\Database\Eloquent\Builder;

trait Searchable
{   
    protected abstract function searchables(): array;

    /** Scope a query to search for a term in the model's searchable attributes. */
    public function scopeSearch(Builder $query, ?string $searchTerm = null, ?array $searchIn = [])
    {
        $searchIn = $searchIn ?: $this->searchables();

        if (empty($searchIn)) {
            return $query;
        }
        
        if (empty($searchTerm)) {
            return $query;
        }

        return $query->where(function ($query) use ($searchIn, $searchTerm) {
            foreach ($searchIn as $field) {
                // Check if field is a relationship (contains a dot)
                if (strpos($field, '.') !== false) {
                    $parts = explode('.', $field);
                    $field = array_pop($parts); // Get the last element (the actual field to search)
                    $relationPath = implode('.', $parts); // Recreate the dot notation path for nested relationships

                    // Use whereHas with dot notation for nested relationships
                    $query->orWhereHas($relationPath, function ($nestedQuery) use ($field, $searchTerm) {
                        $nestedQuery->where($field, 'like', "%{$searchTerm}%");
                    });
                } else {
                    // Regular field search appends table name to avoid conflicts with other tables
                    $query->orWhere($query->getModel()->getTable() . '.' . $field, 'like', "%{$searchTerm}%");
                }
            }
        });
    }
}