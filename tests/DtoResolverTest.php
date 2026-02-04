<?php

declare(strict_types=1);

namespace PhpCollective\LaravelDto\Test;

use Illuminate\Http\Request;
use PhpCollective\LaravelDto\Http\DtoResolver;
use PhpCollective\LaravelDto\Test\Fixtures\DtoResolverTestController;
use PhpCollective\LaravelDto\Test\Fixtures\TestDto;
use PHPUnit\Framework\TestCase;

class DtoResolverTest extends TestCase
{
    public function testCreateFromRequestUsesRequestData(): void
    {
        $request = Request::create('/users', 'POST', ['name' => 'Mark']);

        $dto = DtoResolver::createFromRequest(TestDto::class, $request);

        $this->assertSame(['name' => 'Mark'], $dto->data);
    }

    public function testGetDtoParametersReturnsDtoArguments(): void
    {
        $parameters = DtoResolver::getDtoParameters(DtoResolverTestController::class, 'store');

        $this->assertCount(1, $parameters);
        $this->assertSame('dto', $parameters[0]->getName());
    }
}
