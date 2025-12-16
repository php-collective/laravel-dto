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

## Collection Factory

The service provider automatically configures Laravel collections for DTO collection fields:

```php
// In your DTO with collection fields
$activeRoles = $dto->getRoles()->filter(fn ($role) => $role->isActive());
$roleNames = $dto->getRoles()->pluck('name');
```

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
class StoreUserRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users',
        ];
    }

    public function toDto(): UserDto
    {
        return new UserDto($this->validated());
    }
}

// In controller
public function store(StoreUserRequest $request): JsonResponse
{
    $dto = $request->toDto();
    // ...
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
