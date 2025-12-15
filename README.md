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
];
```

## Usage

### 1. Create your DTO configuration

Create `config/dto.xml` (or `config/dtos.xml` to avoid conflicts):

```xml
<?xml version="1.0" encoding="UTF-8"?>
<dtos xmlns="php-collective-dto">
    <dto name="User">
        <field name="id" type="int"/>
        <field name="name" type="string"/>
        <field name="email" type="string"/>
    </dto>
</dtos>
```

### 2. Generate DTOs

```bash
php artisan dto:generate
```

Options:
- `--dry-run` - Preview changes without writing files
- `-v` - Verbose output

### 3. Use your DTOs

```php
use App\Dto\UserDto;

$user = new UserDto();
$user->setId(1);
$user->setName('John Doe');
$user->setEmail('john@example.com');

return response()->json($user->toArray());
```

Or create from an array:

```php
$user = UserDto::createFromArray([
    'id' => 1,
    'name' => 'John Doe',
    'email' => 'john@example.com',
]);
```

## Supported Config Formats

The package supports multiple config file formats:

- `dto.xml` or `dtos.xml` - XML format
- `dto.yml` / `dto.yaml` or `dtos.yml` / `dtos.yaml` - YAML format
- `dtos.php` - PHP format (use `dtos.php` to avoid conflict with `config/dto.php`)
- `dto/` subdirectory with multiple files

## License

MIT
