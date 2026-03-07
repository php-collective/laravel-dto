# Configuration

## Package Configuration

After publishing the config file with:

```bash
php artisan vendor:publish --provider="PhpCollective\LaravelDto\DtoServiceProvider"
```

You'll have `config/dto.php`:

```php
return [
    /*
    |--------------------------------------------------------------------------
    | DTO Configuration Path
    |--------------------------------------------------------------------------
    |
    | The path where your DTO configuration files are located.
    | Supports dto.php, dto.xml, dto.yml, or dto.yaml
    |
    */
    'config_path' => config_path(),

    /*
    |--------------------------------------------------------------------------
    | DTO Output Path
    |--------------------------------------------------------------------------
    |
    | The path where generated DTO classes will be written.
    |
    */
    'output_path' => app_path('Dto'),

    /*
    |--------------------------------------------------------------------------
    | DTO Namespace
    |--------------------------------------------------------------------------
    |
    | The namespace for generated DTO classes.
    |
    */
    'namespace' => 'App\\Dto',
];
```

## DTO Definition Formats

### PHP Format (default)

Create `config/dto.php`.
This is the default format when running `php artisan dto:init`:

```php
use PhpCollective\Dto\Builder\Dto;
use PhpCollective\Dto\Builder\Field;
use PhpCollective\Dto\Builder\Schema;

return Schema::create()
    ->dto(Dto::create('User')->fields(
        Field::int('id'),
        Field::string('name'),
        Field::string('email')->nullable(),
        Field::array('roles', 'Role'),
    ))
    ->dto(Dto::create('Role')->fields(
        Field::int('id'),
        Field::string('name'),
    ))
    ->toArray();
```

### XML Format

Create `config/dto.xml`:

```xml
<?xml version="1.0" encoding="UTF-8"?>
<dtos xmlns="php-collective-dto">
    <dto name="User">
        <field name="id" type="int"/>
        <field name="name" type="string"/>
        <field name="email" type="string" nullable="true"/>
        <field name="roles" type="Role[]"/>
    </dto>

    <dto name="Role">
        <field name="id" type="int"/>
        <field name="name" type="string"/>
    </dto>
</dtos>
```

### YAML Format

Create `config/dto.yml` or `config/dto.yaml`:

```yaml
User:
  fields:
    id: int
    name: string
    email: string?
    roles: Role[]

Role:
  fields:
    id: int
    name: string
```

## Command Options

The `dto:generate` command supports:

```bash
# Preview changes without writing
php artisan dto:generate --dry-run

# Verbose output
php artisan dto:generate -v

# Both
php artisan dto:generate --dry-run -v
```

## Directory Structure

Recommended structure:

```
app/
├── Dto/
│   ├── UserDto.php          # Generated
│   └── RoleDto.php          # Generated
config/
├── dto.php                  # DTO definitions (or package config if published)
└── dto.xml                  # Alternative: XML format
```

## Multiple Config Files

You can organize DTOs in a subdirectory:

```
config/
└── dto/
    ├── user.xml
    ├── order.xml
    └── product.xml
```

Update config:

```php
'config_path' => config_path('dto'),
```

## Exclude Generated DTOs from Static Analysis

Generated code usually shouldn't run through code-style or static analysis checks.

### PHP_CodeSniffer

Add an exclude pattern to your `phpcs.xml`:

```xml
<rule ref="...">
    <exclude-pattern>app/Dto/*</exclude-pattern>
</rule>
```

### PHPStan

Add an exclude path to your `phpstan.neon`:

```yaml
parameters:
    excludePaths:
        - app/Dto/
```

Alternatively, you can avoid exclusions altogether by generating DTOs into a separate directory outside `app/` (e.g. `generated/`). Update your `config/dto.php`:

```php
'output_path' => base_path('generated'),
```

Then add a PSR-4 autoload entry in your `composer.json`:

```json
{
    "autoload": {
        "psr-4": {
            "App\\Dto\\": "generated/"
        }
    }
}
```

See the base package's [SeparatingGeneratedCode.md](https://github.com/php-collective/dto/blob/master/docs/SeparatingGeneratedCode.md) for details.

## Composer Scripts

You can add convenience scripts to your `composer.json`:

```json
{
    "scripts": {
        "dto:generate": "@php artisan dto:generate",
        "dto:check": "@php artisan dto:generate --dry-run"
    }
}
```

## Further Reading

See the main [php-collective/dto documentation](https://github.com/php-collective/dto) for complete configuration options.
