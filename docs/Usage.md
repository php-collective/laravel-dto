# Usage Guide

## Controller Integration

### From Request Data

```php
use App\Dto\UserDto;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        // From validated request
        $dto = new UserDto($request->validated());

        // Or with ignoreMissing for partial updates
        $dto = UserDto::createFromArray($request->all(), ignoreMissing: true);

        return response()->json($dto->toArray());
    }
}
```

### From Eloquent Models

```php
public function show(int $id): JsonResponse
{
    $user = User::findOrFail($id);
    $dto = new UserDto($user->toArray());

    return response()->json($dto);
}
```

## Collections

The `DtoServiceProvider` automatically registers Laravel's `Illuminate\Support\Collection` as the collection type for DTO collection fields. No manual setup is needed.

### Defining Collection Fields

In your DTO config, use the `[]` suffix to define collection fields:

```xml
<dto name="User">
    <field name="id" type="int"/>
    <field name="name" type="string"/>
    <field name="roles" type="Role[]"/>
    <field name="tags" type="string[]"/>
</dto>
```

After generating, collection fields will use Laravel's `Collection` class:

```php
$user = new UserDto([
    'id' => 1,
    'name' => 'John',
    'roles' => [
        ['name' => 'admin', 'active' => true],
        ['name' => 'editor', 'active' => false],
    ],
    'tags' => ['vip', 'premium'],
]);

// Collection methods are available
$activeRoles = $user->getRoles()->filter(fn (RoleDto $role) => $role->getActive());
$roleNames = $user->getRoles()->map(fn (RoleDto $role) => $role->getName());
$tagCount = $user->getTags()->count();
```

### What the Adapter Does

The service provider performs two registrations on boot:

1. **Runtime collection factory** — `Dto::setCollectionFactory(fn (array $items) => collect($items))` ensures that collection fields are hydrated as `Illuminate\Support\Collection` instances at runtime.
2. **Code generation adapter** — `CollectionAdapterRegistry::register(new LaravelCollectionAdapter())` ensures that generated DTO code uses `collect([])` and `->push()` for collection initialization and appending.

Both are required: the factory handles runtime hydration from arrays, while the adapter controls the generated PHP code.

## Validation

Validate DTOs using Laravel's validator:

```php
use Illuminate\Support\Facades\Validator;

$dto = new UserDto($request->all(), ignoreMissing: true);

$validator = Validator::make($dto->toArray(), [
    'name' => 'required|string|max:255',
    'email' => 'required|email|unique:users',
]);

if ($validator->fails()) {
    return response()->json(['errors' => $validator->errors()], 422);
}
```

## Form Requests

Combine with Form Requests for clean validation:

```php
// app/Http/Requests/StoreUserRequest.php
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

// In controller
public function store(StoreUserRequest $request): JsonResponse
{
    $dto = $request->toDto();
    // ...
}
```

If you want to opt into it on a per-request basis, you can use the trait instead:

```php
use PhpCollective\LaravelDto\Http\CreatesDto;
use Illuminate\Foundation\Http\FormRequest;

class StoreUserRequest extends FormRequest
{
    use CreatesDto;

    protected string $dtoClass = UserDto::class;
}
```

### Automatic DTO injection

Register the resolver once (e.g. in `AppServiceProvider::boot()`):

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

## API Resources

Use DTOs with API Resources:

```php
// app/Http/Resources/UserResource.php
class UserResource extends JsonResource
{
    public function toArray($request): array
    {
        $dto = new UserDto($this->resource->toArray());
        return $dto->toArray();
    }
}
```

## Service Layer Pattern

```php
// app/Services/UserService.php
class UserService
{
    public function createUser(UserDto $dto): User
    {
        return User::create($dto->toArray());
    }

    public function updateUser(User $user, UserDto $dto): User
    {
        $user->update($dto->toArray());
        return $user->fresh();
    }
}
```

## Nested DTOs

When your DTO has nested DTO fields:

```php
// config/dtos.xml
<dto name="Order">
    <field name="id" type="int"/>
    <field name="customer" type="Customer"/>
    <field name="items" type="OrderItem[]"/>
</dto>

// Usage
$order = new OrderDto([
    'id' => 1,
    'customer' => ['name' => 'John', 'email' => 'john@example.com'],
    'items' => [
        ['product' => 'Widget', 'quantity' => 2],
        ['product' => 'Gadget', 'quantity' => 1],
    ],
]);

// Access nested data
$customerName = $order->getCustomer()->getName();
$totalItems = $order->getItems()->count();
```

## Further Reading

See the main [php-collective/dto documentation](https://github.com/php-collective/dto) for:
- DTO configuration options
- Type support
- Custom casters
- Advanced patterns
