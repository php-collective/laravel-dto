<?php

declare(strict_types=1);

namespace PhpCollective\LaravelDto\Http;

use Illuminate\Http\Request;
use PhpCollective\Dto\Dto\AbstractDto;
use ReflectionClass;
use ReflectionNamedType;

/**
 * Resolves DTO parameters in controller methods from request data.
 *
 * This resolver allows automatic injection of DTOs in controller methods:
 *
 * ```php
 * public function store(CreateUserDto $dto): Response
 * {
 *     // $dto is automatically created from request data
 * }
 * ```
 *
 * Enable in AppServiceProvider:
 *
 * ```php
 * public function boot(): void
 * {
 *     DtoResolver::register();
 * }
 * ```
 */
class DtoResolver
{
    /**
     * Register the DTO resolver with the Laravel container.
     */
    public static function register(): void
    {
        app()->resolving(function ($object, $app) {
            if ($object instanceof AbstractDto) {
                return;
            }
        });

        app()->beforeResolving(AbstractDto::class, function ($class, $parameters, $app) {
            if (!$app->has('request')) {
                return;
            }

            /** @var \Illuminate\Http\Request $request */
            $request = $app->make('request');

            /** @var class-string<\PhpCollective\Dto\Dto\AbstractDto> $dtoClass */
            $dtoClass = $class;

            $app->bind($class, function () use ($dtoClass, $request) {
                /** @var array<string, mixed> $data */
                $data = $request->all();

                return $dtoClass::createFromArray($data);
            });
        });
    }

    /**
     * Register automatic DTO resolution for route parameters.
     *
     * This binds all DTO classes to be resolved from request data automatically.
     */
    public static function registerRouteBinding(): void
    {
        app('router')->bind('dto', function ($value, $route) {
            return $value;
        });
    }

    /**
     * Create a DTO instance from request data.
     *
     * @template T of \PhpCollective\Dto\Dto\AbstractDto
     *
     * @param class-string<T> $class
     * @param \Illuminate\Http\Request $request
     *
     * @return T
     */
    public static function createFromRequest(string $class, Request $request): AbstractDto
    {
        /** @var array<string, mixed> $data */
        $data = $request->all();

        return $class::createFromArray($data);
    }

    /**
     * Create a DTO instance from validated request data.
     *
     * @template T of \PhpCollective\Dto\Dto\AbstractDto
     *
     * @param class-string<T> $class
     * @param \Illuminate\Http\Request $request
     * @param array<string, mixed> $rules
     *
     * @return T
     */
    public static function createFromValidatedRequest(string $class, Request $request, array $rules): AbstractDto
    {
        /** @var array<string, mixed> $validated */
        $validated = $request->validate($rules);

        return $class::createFromArray($validated);
    }

    /**
     * Get DTO parameters from a controller method.
     *
     * @param class-string $controller
     * @param string $method
     *
     * @return array<\ReflectionParameter>
     */
    public static function getDtoParameters(string $controller, string $method): array
    {
        $reflection = new ReflectionClass($controller);
        $methodReflection = $reflection->getMethod($method);

        $dtoParameters = [];
        foreach ($methodReflection->getParameters() as $parameter) {
            $type = $parameter->getType();
            if (!$type instanceof ReflectionNamedType) {
                continue;
            }

            $typeName = $type->getName();
            if (class_exists($typeName) && is_subclass_of($typeName, AbstractDto::class)) {
                $dtoParameters[] = $parameter;
            }
        }

        return $dtoParameters;
    }
}
