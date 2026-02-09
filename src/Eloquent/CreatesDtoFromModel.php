<?php

declare(strict_types=1);

namespace PhpCollective\LaravelDto\Eloquent;

use InvalidArgumentException;
use PhpCollective\Dto\Dto\AbstractDto;

trait CreatesDtoFromModel
{
    /**
     * @template T of \PhpCollective\Dto\Dto\AbstractDto
     *
     * @param class-string<T>|null $dtoClass
     * @param bool $includeRelations
     * @param bool $ignoreMissing
     * @param string|null $type
     *
     * @throws \InvalidArgumentException
     *
     * @return T
     */
    public function toDto(
        ?string $dtoClass = null,
        bool $includeRelations = true,
        bool $ignoreMissing = false,
        ?string $type = null,
    ): AbstractDto {
        $class = $dtoClass ?? $this->getDtoClass();

        if ($class === null) {
            throw new InvalidArgumentException('No DTO class specified. Set $dtoClass or pass one to toDto().');
        }

        $data = $includeRelations ? $this->toArray() : $this->attributesToArray();

        return $class::createFromArray(
            data: $data,
            ignoreMissing: $ignoreMissing,
            type: $type,
        );
    }

    /**
     * @return class-string<\PhpCollective\Dto\Dto\AbstractDto>|null
     */
    protected function getDtoClass(): ?string
    {
        return null;
    }
}
