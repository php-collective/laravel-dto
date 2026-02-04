<?php

declare(strict_types=1);

namespace PhpCollective\LaravelDto\Test\Fixtures;

class DtoResolverTestController
{
    public function store(TestDto $dto, string $name): void
    {
    }
}
