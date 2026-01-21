<?php

declare(strict_types=1);

namespace PhpCollective\LaravelDto\Collection;

use PhpCollective\Dto\Collection\CollectionAdapterInterface;

/**
 * Adapter for Laravel's Collection class.
 *
 * Illuminate\Support\Collection is mutable - push() modifies in place and returns $this.
 */
class LaravelCollectionAdapter implements CollectionAdapterInterface
{
    /**
     * @inheritDoc
     */
    public function getCollectionClass(): string
    {
        return '\\Illuminate\\Support\\Collection';
    }

    /**
     * @inheritDoc
     */
    public function isImmutable(): bool
    {
        return false;
    }

    /**
     * @inheritDoc
     */
    public function getAppendMethod(): string
    {
        return 'push';
    }

    /**
     * @inheritDoc
     */
    public function getCreateEmptyCode(string $typeHint): string
    {
        // Use Laravel's collect() helper for idiomatic code
        return 'collect([])';
    }

    /**
     * @inheritDoc
     */
    public function getAppendCode(string $collectionVar, string $itemVar): string
    {
        return "{$collectionVar}->push({$itemVar});";
    }
}
