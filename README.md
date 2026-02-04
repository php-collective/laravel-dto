# Laravel DTO

Laravel integration for [php-collective/dto](https://github.com/php-collective/dto).

## Installation

```bash
composer require php-collective/laravel-dto
```

The service provider will be auto-discovered.

## Configuration

Publish the config file:

```bash
php artisan vendor:publish --provider="PhpCollective\LaravelDto\DtoServiceProvider"
```

This creates `config/dto.php` with the following options:

```php
return [
    'config_path' => config_path(),     // Where DTO config files are located
    'output_path' => app_path('Dto'),   // Where to generate DTOs
    'namespace' => 'App\\Dto',          // Namespace for generated DTOs
    'typescript_output_path' => resource_path('js/types'), // TypeScript output
    'jsonschema_output_path' => resource_path('schemas'),  // JSON Schema output
];
```

## Usage

### 1. Initialize DTO configuration

```bash
php artisan dto:init
```

This creates a `config/dtos.php` file with a sample DTO definition (PHP format is the default).
You can also use `--format=xml` or `--format=yaml`.

The generated config looks like:

```php
use PhpCollective\Dto\Builder\Dto;
use PhpCollective\Dto\Builder\Field;
use PhpCollective\Dto\Builder\Schema;

return Schema::create()
    ->dto(Dto::create('User')->fields(
        Field::int('id'),
        Field::string('name'),
        Field::string('email')->nullable(),
    ))
    ->toArray();
```

### 2. Generate DTOs

```bash
php artisan dto:generate
```

Options:
- `--dry-run` - Preview changes without writing files
- `-v` - Verbose output

### 3. Generate TypeScript interfaces

```bash
php artisan dto:typescript
php artisan dto:typescript --multiple-files --readonly
```

### 4. Generate JSON Schema

```bash
php artisan dto:jsonschema
php artisan dto:jsonschema --multiple-files
```

### 5. Use your DTOs

```php
use App\Dto\UserDto;

$user = new UserDto([
    'id' => 1,
    'name' => 'John Doe',
    'email' => 'john@example.com',
]);

return response()->json($user->toArray());
```

## Request Integration

### DtoFormRequest

```php
use PhpCollective\LaravelDto\Http\DtoFormRequest;

class StoreUserRequest extends DtoFormRequest
{
    protected string $dtoClass = UserDto::class;

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users',
        ];
    }
}

// In controller:
public function store(StoreUserRequest $request): JsonResponse
{
    $dto = $request->toDto();
    // ...
}
```

### DtoResolver

Register once (e.g. in `AppServiceProvider::boot()`):

```php
use PhpCollective\LaravelDto\Http\DtoResolver;

DtoResolver::register();
```

Then inject DTOs directly:

```php
public function store(UserDto $dto): JsonResponse
{
    // $dto is built from request data
}
```

## Collections

The service provider automatically registers Laravel's `Illuminate\Support\Collection` for DTO collection fields. Define collection fields with the `[]` suffix:

```php
Field::array('roles', 'Role'),  // Role[] collection
Field::array('tags', 'string'), // string[] collection
```

After generating, collection fields use Laravel's `Collection` class with all its methods (`filter`, `map`, `pluck`, etc.).

## Supported Config Formats

The package supports multiple config file formats:

- `dtos.php` - PHP format (default, use `dtos.php` to avoid conflict with `config/dto.php`)
- `dto.xml` or `dtos.xml` - XML format
- `dto.yml` / `dto.yaml` or `dtos.yml` / `dtos.yaml` - YAML format
- `dto/` subdirectory with multiple files

## License

MIT
