<?php

declare(strict_types=1);

namespace PhpCollective\LaravelDto\Http;

use InvalidArgumentException;
use PhpCollective\Dto\Dto\AbstractDto;

/**
 * Trait for FormRequest classes to create DTOs from validated request data.
 *
 * Usage:
 *
 * ```php
 * class CreateUserRequest extends FormRequest
 * {
 *     use CreatesDto;
 *
 *     protected string $dtoClass = CreateUserDto::class;
 *
 *     public function rules(): array
 *     {
 *         return [
 *             'name' => 'required|string|max:255',
 *             'email' => 'required|email',
 *         ];
 *     }
 * }
 *
 * // In controller:
 * public function store(CreateUserRequest $request): Response
 * {
 *     $dto = $request->toDto();
 *     // or with explicit class:
 *     $dto = $request->toDto(CreateUserDto::class);
 * }
 * ```
 */
trait CreatesDto
{
    /**
     * Create a DTO from the validated request data.
     *
     * @template T of \PhpCollective\Dto\Dto\AbstractDto
     *
     * @param class-string<T>|null $dtoClass The DTO class to create. If null, uses $this->dtoClass property.
     *
     * @throws \InvalidArgumentException If no DTO class is specified.
     *
     * @return T
     */
    public function toDto(?string $dtoClass = null): AbstractDto
    {
        /** @var class-string<\PhpCollective\Dto\Dto\AbstractDto>|null $class */
        $class = $dtoClass ?? $this->dtoClass ?? null;

        if ($class === null) {
            throw new InvalidArgumentException(
                'No DTO class specified. Either pass a class to toDto() or set the $dtoClass property.',
            );
        }

        /** @var array<string, mixed> $validated */
        $validated = $this->validated();

        // @phpstan-ignore return.type (template type T is properly constrained but PHPStan can't verify the return)
        return $class::createFromArray($validated);
    }

    /**
     * Create a DTO from all request data (not just validated).
     *
     * Use with caution - prefer toDto() with validation.
     *
     * @template T of \PhpCollective\Dto\Dto\AbstractDto
     *
     * @param class-string<T> $dtoClass
     *
     * @return T
     */
    public function toDtoFromAll(string $dtoClass): AbstractDto
    {
        /** @var array<string, mixed> $all */
        $all = $this->all();

        return $dtoClass::createFromArray($all);
    }

    /**
     * Create a DTO from specific request input keys.
     *
     * @template T of \PhpCollective\Dto\Dto\AbstractDto
     *
     * @param class-string<T> $dtoClass
     * @param array<string> $keys
     *
     * @return T
     */
    public function toDtoFromOnly(string $dtoClass, array $keys): AbstractDto
    {
        /** @var array<string, mixed> $only */
        $only = $this->only($keys);

        return $dtoClass::createFromArray($only);
    }
}
