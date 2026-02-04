<?php

declare(strict_types=1);

namespace PhpCollective\LaravelDto\Test\Fixtures;

use PhpCollective\Dto\Dto\AbstractDto;

class TestDto extends AbstractDto
{
    /**
     * @var array<string, mixed>
     */
    public array $data = [];

    /**
     * @param array<string, mixed> $data
     */
    public function __construct(array $data = [])
    {
        $this->data = $data;
    }

    /**
     * @param array<string, mixed> $data
     * @param bool $ignoreMissing
     * @param string|null $type
     *
     * @return static
     */
    public static function createFromArray(array $data, bool $ignoreMissing = false, ?string $type = null): static
    {
        return new static($data);
    }

    /**
     * @param string|null $type
     * @param array<string>|null $fields
     * @param bool $touched
     *
     * @return array<string, mixed>
     */
    public function toArray(?string $type = null, ?array $fields = null, bool $touched = false): array
    {
        return $this->data;
    }
}
