<?php

declare(strict_types=1);

namespace PhpCollective\LaravelDto\Eloquent;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\AbstractCursorPaginator;
use Illuminate\Pagination\AbstractPaginator;
use Illuminate\Support\Collection;
use InvalidArgumentException;
use PhpCollective\Dto\Dto\AbstractDto;

final class DtoMapper
{
    private function __construct()
    {
    }

    /**
     * @param \Illuminate\Database\Eloquent\Model $model
     * @param class-string<\PhpCollective\Dto\Dto\AbstractDto> $dtoClass
     * @param list<string> $relations
     * @param bool $includeRelations
     * @param string|null $type
     * @param bool $ignoreMissing
     */
    public static function fromModel(
        Model $model,
        string $dtoClass,
        array $relations = [],
        bool $ignoreMissing = false,
        ?string $type = null,
        bool $includeRelations = true,
    ): AbstractDto {
        if ($relations !== []) {
            $model->loadMissing($relations);
        }

        $data = $includeRelations ? $model->toArray() : $model->attributesToArray();

        return $dtoClass::createFromArray(
            data: $data,
            ignoreMissing: $ignoreMissing,
            type: $type,
        );
    }

    /**
     * @param iterable<int, \Illuminate\Database\Eloquent\Model|array<string, mixed>|\Illuminate\Contracts\Support\Arrayable<array-key, mixed>> $items
     * @param class-string<\PhpCollective\Dto\Dto\AbstractDto> $dtoClass
     * @param list<string> $relations
     * @param bool $ignoreMissing
     * @param string|null $type
     * @param bool $includeRelations
     *
     * @return \Illuminate\Support\Collection<int, \PhpCollective\Dto\Dto\AbstractDto>
     */
    public static function fromCollection(
        iterable $items,
        string $dtoClass,
        array $relations = [],
        bool $ignoreMissing = false,
        ?string $type = null,
        bool $includeRelations = true,
    ): Collection {
        $collection = $items instanceof Collection ? $items : collect($items);

        if ($relations !== [] && $collection instanceof EloquentCollection) {
            $collection->loadMissing($relations);
        }

        return $collection->map(function (mixed $item) use ($dtoClass, $ignoreMissing, $type, $includeRelations): AbstractDto {
            if ($item instanceof Model) {
                $data = $includeRelations ? $item->toArray() : $item->attributesToArray();

                return $dtoClass::createFromArray(
                    data: $data,
                    ignoreMissing: $ignoreMissing,
                    type: $type,
                );
            }

            if ($item instanceof Arrayable) {
                $item = $item->toArray();
            }

            if (is_array($item)) {
                return $dtoClass::createFromArray(
                    data: $item,
                    ignoreMissing: $ignoreMissing,
                    type: $type,
                );
            }

            throw new InvalidArgumentException('DTO mapper expects models, arrays, or Arrayable values.');
        });
    }

    /**
     * @param \Illuminate\Pagination\AbstractPaginator<array-key, mixed>|\Illuminate\Pagination\AbstractCursorPaginator<array-key, mixed> $paginator
     * @param class-string<\PhpCollective\Dto\Dto\AbstractDto> $dtoClass
     * @param list<string> $relations
     * @param bool $ignoreMissing
     * @param string|null $type
     * @param bool $includeRelations
     *
     * @return \Illuminate\Pagination\AbstractPaginator<array-key, mixed>|\Illuminate\Pagination\AbstractCursorPaginator<array-key, mixed>
     */
    public static function fromPaginator(
        AbstractPaginator|AbstractCursorPaginator $paginator,
        string $dtoClass,
        array $relations = [],
        bool $ignoreMissing = false,
        ?string $type = null,
        bool $includeRelations = true,
    ): AbstractPaginator|AbstractCursorPaginator {
        $mapped = self::fromCollection(
            items: $paginator->getCollection(),
            dtoClass: $dtoClass,
            relations: $relations,
            ignoreMissing: $ignoreMissing,
            type: $type,
            includeRelations: $includeRelations,
        );

        $paginator->setCollection($mapped);

        return $paginator;
    }
}
