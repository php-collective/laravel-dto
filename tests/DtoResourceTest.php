<?php

declare(strict_types=1);

namespace PhpCollective\LaravelDto\Test;

use Illuminate\Http\Request;
use Orchestra\Testbench\TestCase;
use PhpCollective\LaravelDto\Http\DtoResource;
use PhpCollective\LaravelDto\Test\Fixtures\TestDto;

class DtoResourceTest extends TestCase
{
    public function testDtoResourceReturnsDtoArray(): void
    {
        $dto = new TestDto(['name' => 'Mark']);
        $resource = new DtoResource($dto);

        $data = $resource->toArray(Request::create('/'));

        $this->assertSame(['name' => 'Mark'], $data);
    }

    public function testDtoResourceCollectionReturnsDtoArrays(): void
    {
        $collection = DtoResource::collection([
            new TestDto(['name' => 'Anna']),
        ]);

        $data = $collection->toArray(Request::create('/'));

        $this->assertSame([['name' => 'Anna']], $data);
    }
}
