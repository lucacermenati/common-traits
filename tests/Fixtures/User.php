<?php

declare(strict_types=1);

namespace LucaCermenati\CommonTraits\Tests\Fixtures;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use LucaCermenati\CommonTraits\Traits\Filterable;
use LucaCermenati\CommonTraits\Traits\Searchable;
use LucaCermenati\CommonTraits\Traits\Sortable;

class User extends Model
{
    use Filterable;
    use Searchable;
    use Sortable;

    protected $table = 'users';

    protected $guarded = [];

    protected function searchables(): array
    {
        return ['name', 'email', 'posts.title'];
    }

    public function posts(): HasMany
    {
        return $this->hasMany(Post::class);
    }
}
