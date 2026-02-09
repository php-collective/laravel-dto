<?php

declare(strict_types=1);

namespace PhpCollective\LaravelDto\Eloquent;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Database\Eloquent\Model;
use InvalidArgumentException;
use PhpCollective\Dto\Dto\AbstractDto;
use PhpCollective\Dto\Utility\Json;

/**
 * @implements \Illuminate\Contracts\Database\Eloquent\CastsAttributes<\PhpCollective\Dto\Dto\AbstractDto|null, mixed>
 */
class DtoCast implements CastsAttributes
{
    /**
     * @param class-string<\PhpCollective\Dto\Dto\AbstractDto> $dtoClass
     * @param bool $ignoreMissing
     * @param string|null $type
     */
    public function __construct(
        private string $dtoClass,
        private bool $ignoreMissing = false,
        private ?string $type = null,
    ) {
    }

    public function get(Model $model, string $key, mixed $value, array $attributes): ?AbstractDto
    {
        if ($value === null) {
            return null;
        }

        $data = $this->normalizeValue($value, $key);

        return $this->dtoClass::createFromArray(
            data: $data,
            ignoreMissing: $this->ignoreMissing,
            type: $this->type,
        );
    }

    public function set(Model $model, string $key, mixed $value, array $attributes): mixed
    {
        if ($value === null) {
            return null;
        }

        if ($value instanceof AbstractDto) {
            return (new Json())->encode($value->toArray($this->type));
        }

        if ($value instanceof Arrayable) {
            return (new Json())->encode($value->toArray());
        }

        if (is_array($value)) {
            return (new Json())->encode($value);
        }

        throw new InvalidArgumentException("DTO cast for [{$key}] expects a DTO, array, or Arrayable value.");
    }

    public function serialize(Model $model, string $key, mixed $value, array $attributes): mixed
    {
        if ($value === null) {
            return null;
        }

        if ($value instanceof AbstractDto) {
            return $value->toArray($this->type);
        }

        return $this->normalizeValue($value, $key);
    }

    /**
     * @throws \InvalidArgumentException
     *
     * @return array<string, mixed>
     */
    private function normalizeValue(mixed $value, string $key): array
    {
        if ($value instanceof AbstractDto) {
            return $value->toArray($this->type);
        }

        if ($value instanceof Arrayable) {
            return $value->toArray();
        }

        if (is_array($value)) {
            return $value;
        }

        if (is_string($value)) {
            $decoded = (new Json())->decode($value, true);
            if (!is_array($decoded)) {
                throw new InvalidArgumentException("DTO cast for [{$key}] expects a JSON object.");
            }

            return $decoded;
        }

        throw new InvalidArgumentException("DTO cast for [{$key}] expects a JSON string, array, or DTO.");
    }
}
