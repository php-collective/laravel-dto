<?php

declare(strict_types=1);

namespace PhpCollective\LaravelDto\Http;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Base FormRequest class that automatically creates DTOs from validated data.
 *
 * Usage:
 *
 * ```php
 * class CreateUserRequest extends DtoFormRequest
 * {
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
 * }
 * ```
 */
abstract class DtoFormRequest extends FormRequest
{
    use CreatesDto;

    /**
     * The DTO class to create from validated data.
     *
     * @var class-string<\PhpCollective\Dto\Dto\AbstractDto>|null
     */
    protected ?string $dtoClass = null;
}
