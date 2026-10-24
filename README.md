# Common Traits

Small, reusable Eloquent traits for Laravel applications:

| Trait | What it gives your model |
|---|---|
| [`Searchable`](#searchable) | A `search()` scope that matches a term against columns and relationships |
| [`Filterable`](#filterable) | A `filter()` scope that maps request inputs to methods on a filter class |
| [`Sortable`](#sortable) | A `sort()` scope that maps a `?sort=` input to methods on a sorter class |
| [`HasAttachments`](#hasattachments) | `uploadFile()` and `removeFile()` helpers for a file path stored on the model |

Requires PHP 8.2+ and Laravel 11.17 or 12.

## Installation

```bash
composer require lucacermenati/common-traits
```

If the package is not published on Packagist, add the repository to your `composer.json` first:

```json
"repositories": [
    { "type": "vcs", "url": "https://github.com/lucacermenati/common-traits" }
]
```

There is nothing to publish or configure. Each trait is opt-in per model.

## Searchable

Adds a `search()` scope. The model declares which columns are searchable; a dotted name searches through a relationship.

```php
use Illuminate\Database\Eloquent\Model;
use LucaCermenati\CommonTraits\Traits\Searchable;

class User extends Model
{
    use Searchable;

    protected function searchables(): array
    {
        return ['name', 'email', 'company.name', 'posts.title'];
    }
}
```

```php
// Users whose name, email, company name or any post title contains "luca"
User::search('luca')->get();

// Search only the given columns, ignoring searchables()
User::search('luca', ['email'])->get();

// An empty or null term leaves the query untouched
User::search($request->input('q'))->paginate();
```

Details:

- Matching is partial (`%term%`) and case-insensitive on every supported driver. Postgres uses `ILIKE`, others use `LIKE`.
- Plain columns are qualified with the model's table, so the scope is safe to combine with joins.
- A dotted name such as `posts.title` becomes a `whereHas('posts')` on the `title` column. Nested paths such as `company.owner.name` work too.
- The scope is one grouped `OR` block, so it composes with other `where` clauses as expected.

## Filterable

Adds a `filter()` scope that hands the query and the request to a filter class. The filter class extends `QueryFilter`, and each public method on it is a filter reachable from the request.

```php
use LucaCermenati\CommonTraits\Filters\QueryFilter;

class UserFilter extends QueryFilter
{
    public function name(string $value): void
    {
        $this->builder->where('name', 'like', "%{$value}%");
    }

    public function createdFrom(string $value): void
    {
        $this->builder->where('created_at', '>=', $value);
    }

    public function role(string $value): void
    {
        $this->builder->whereHas('roles', fn ($q) => $q->where('slug', $value));
    }
}
```

```php
use LucaCermenati\CommonTraits\Traits\Filterable;

class User extends Model
{
    use Filterable;
}
```

```php
// GET /users?name=luca&created_from=2026-01-01&role=admin
public function index(Request $request)
{
    return User::filter(new UserFilter, $request)->paginate();
}
```

How inputs map to methods:

- Each input key is converted to camelCase: `created_from` calls `createdFrom()`.
- The method receives the raw input value as its only argument.
- Inputs with an empty value are skipped, so `?name=` applies no filter.
- Inputs that do not match a method on your filter class are ignored.
- Methods of `QueryFilter` itself, such as `apply`, are never reachable from the request.

The filter is a plain class, so you can inject dependencies through its constructor and resolve it from the container:

```php
User::filter(app(UserFilter::class), $request)->get();
```

## Sortable

Adds a `sort()` scope that hands the query and the request to a sorter class. The sorter extends `QuerySorter`, and each public method is a sort option reachable from the request.

```php
use LucaCermenati\CommonTraits\Sorters\QuerySorter;

class UserSorter extends QuerySorter
{
    public function lastName(string $direction): void
    {
        $this->builder->orderBy('last_name', $direction);
    }

    public function company(string $direction): void
    {
        $this->builder
            ->join('companies', 'companies.id', '=', 'users.company_id')
            ->orderBy('companies.name', $direction)
            ->select('users.*');
    }
}
```

```php
use LucaCermenati\CommonTraits\Traits\Sortable;

class User extends Model
{
    use Sortable;
}
```

```php
// GET /users?sort=last_name      -> last name ascending
// GET /users?sort=-last_name     -> last name descending
// GET /users?sort=company        -> company name ascending
// GET /users                     -> default order only
User::sort(new UserSorter, $request)->get();
```

How the input maps to a method:

- The `sort` value is converted to camelCase: `last_name` calls `lastName()`.
- A leading minus flips the direction, which is passed to the method as `'asc'` or `'desc'`.
- An unknown or missing value applies only the default order.
- The default order is always appended last as a tiebreaker, so rows with equal sort values come back in a stable order.

Customise the defaults by overriding the properties:

```php
class UserSorter extends QuerySorter
{
    protected string $sortInput = 'order_by';   // query string key, default 'sort'
    protected string $defaultColumn = 'id';     // default 'created_at'
    protected string $defaultDirection = 'asc'; // default 'desc'
}
```

The default column is qualified with the model's table automatically, so a sorter that joins other tables will not produce an ambiguous column error.

### Combining search, filter and sort

The scopes are independent and chain naturally:

```php
User::query()
    ->search($request->input('q'))
    ->filter(new UserFilter, $request)
    ->sort(new UserSorter, $request)
    ->paginate();
```

## HasAttachments

Helpers for a model that stores a file path, and optionally the original file name, in its own columns.

```php
use LucaCermenati\CommonTraits\Traits\HasAttachments;

class Document extends Model
{
    use HasAttachments;
}
```

Migration:

```php
$table->string('path')->nullable();
$table->string('original_name')->nullable();
```

Upload a file. The new file is stored first, the previous file on the same attribute is deleted, and the model is saved:

```php
public function store(Request $request, Document $document)
{
    $document->uploadFile(
        file: $request->file('attachment'),
        directory: 'documents',
        pathAttribute: 'path',
        nameAttribute: 'original_name', // optional
        disk: 'public',                 // optional, default 'public'
    );
}
```

Remove the file and clear the attributes:

```php
$document->removeFile('path', 'original_name');
```

Both methods call `save()` on the model, so no further action is needed. Because the new file is stored before the old one is deleted, a failed upload leaves the existing file and the model untouched.

## Testing

```bash
composer install
composer test
```

The suite runs on Orchestra Testbench with an in-memory SQLite database. Each test seeds a few rows, runs a scope and asserts on the rows returned.

## License

MIT
