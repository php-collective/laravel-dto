<?php

declare(strict_types=1);

namespace PhpCollective\LaravelDto\Eloquent;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use InvalidArgumentException;
use PhpCollective\Dto\Dto\AbstractDto;
use PhpCollective\Dto\Utility\Json;

/**
 * @implements \Illuminate\Contracts\Database\Eloquent\CastsAttributes<\Illuminate\Support\Collection|null, mixed>
 */
class DtoCollectionCast implements CastsAttributes
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

    /**
     * @param \Illuminate\Database\Eloquent\Model $model
     * @param string $key
     * @param mixed $value
     * @param array<string, mixed> $attributes
     *
     * @return \Illuminate\Support\Collection<int, \PhpCollective\Dto\Dto\AbstractDto>|null
     */
    public function get(Model $model, string $key, mixed $value, array $attributes): ?Collection
    {
        if ($value === null) {
            return null;
        }

        $data = $this->normalizeArrayValue($value, $key);
        $items = [];

        foreach ($data as $item) {
            $items[] = $this->createDto($item, $key);
        }

        return collect($items);
    }

    public function set(Model $model, string $key, mixed $value, array $attributes): mixed
    {
        if ($value === null) {
            return null;
        }

        if ($value instanceof Collection) {
            $value = $value->all();
        }

        if ($value instanceof Arrayable) {
            $value = $value->toArray();
        }

        if (!is_array($value)) {
            throw new InvalidArgumentException("DTO collection cast for [{$key}] expects an array or Collection.");
        }

        $items = [];
        foreach ($value as $item) {
            $items[] = $this->normalizeItem($item, $key);
        }

        return (new Json())->encode($items);
    }

    /**
     * @param \Illuminate\Database\Eloquent\Model $model
     * @param string $key
     * @param mixed $value
     * @param array<string, mixed> $attributes
     *
     * @throws \InvalidArgumentException
     */
    public function serialize(Model $model, string $key, mixed $value, array $attributes): mixed
    {
        if ($value === null) {
            return null;
        }

        if ($value instanceof Collection) {
            $value = $value->all();
        }

        if ($value instanceof Arrayable) {
            $value = $value->toArray();
        }

        if (!is_array($value)) {
            throw new InvalidArgumentException("DTO collection cast for [{$key}] expects an array or Collection.");
        }

        $items = [];
        foreach ($value as $item) {
            $items[] = $this->normalizeItem($item, $key);
        }

        return $items;
    }

    /**
     * @throws \InvalidArgumentException
     *
     * @return array<int, mixed>
     */
    private function normalizeArrayValue(mixed $value, string $key): array
    {
        if ($value instanceof Collection) {
            return $value->all();
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
                throw new InvalidArgumentException("DTO collection cast for [{$key}] expects a JSON array.");
            }

            return $decoded;
        }

        throw new InvalidArgumentException("DTO collection cast for [{$key}] expects a JSON string, array, or Collection.");
    }

    /**
     * @throws \InvalidArgumentException
     *
     * @return array<string, mixed>
     */
    private function normalizeItem(mixed $item, string $key): array
    {
        if ($item instanceof AbstractDto) {
            return $item->toArray($this->type);
        }

        if ($item instanceof Arrayable) {
            return $item->toArray();
        }

        if (is_array($item)) {
            return $item;
        }

        throw new InvalidArgumentException("DTO collection cast for [{$key}] expects array items or DTO instances.");
    }

    private function createDto(mixed $item, string $key): AbstractDto
    {
        $data = $this->normalizeItem($item, $key);

        return $this->dtoClass::createFromArray(
            data: $data,
            ignoreMissing: $this->ignoreMissing,
            type: $this->type,
        );
    }
}
