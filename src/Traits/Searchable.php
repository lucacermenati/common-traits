<?php

namespace LucaCermenati\CommonTraits\Traits;

trait Searchable
{
    protected array $search = [];
    
    /** Scope a query to search for a term in the model's searchable attributes. */
    public function scopeSearch($query, $search)
    {
        if (empty($search) || empty($this->search)) {
            return $query;
        }

        return $query->where(function ($query) use ($search) {
            foreach ($this->search as $field) {
                // Check if field is a relationship (contains a dot)
                if (strpos($field, '.') !== false) {
                    $parts = explode('.', $field);
                    $field = array_pop($parts); // Get the last element (the actual field to search)
                    $relationPath = implode('.', $parts); // Recreate the dot notation path for nested relationships

                    // Use whereHas with dot notation for nested relationships
                    $query->orWhereHas($relationPath, function ($nestedQuery) use ($field, $search) {
                        $nestedQuery->where($field, 'like', "%{$search}%");
                    });
                } else {
                    // Regular field search appends table name to avoid conflicts with other tables
                    $query->orWhere($query->getModel()->getTable() . '.' . $field, 'like', "%{$search}%");
                }
            }
        });
    }
}