<?php

declare(strict_types=1);

namespace PhpCollective\LaravelDto\Test;

use Illuminate\Foundation\Http\FormRequest;
use InvalidArgumentException;
use Orchestra\Testbench\TestCase;
use PhpCollective\LaravelDto\DtoServiceProvider;
use PhpCollective\LaravelDto\Http\CreatesDto;
use PhpCollective\LaravelDto\Http\DtoFormRequest;
use PhpCollective\LaravelDto\Test\Fixtures\CreatesDtoTestFormRequest;
use ReflectionClass;

class CreatesDtoTest extends TestCase
{
    /**
     * @param \Illuminate\Foundation\Application $app
     *
     * @return array<int, class-string>
     */
    protected function getPackageProviders($app): array
    {
        return [
            DtoServiceProvider::class,
        ];
    }

    public function testTraitExistsAndIsUsable(): void
    {
        $this->assertTrue(trait_exists(CreatesDto::class));
    }

    public function testDtoFormRequestExtends(): void
    {
        $reflection = new ReflectionClass(DtoFormRequest::class);

        $this->assertTrue($reflection->isAbstract());
        $this->assertTrue($reflection->isSubclassOf(FormRequest::class));
    }

    public function testDtoFormRequestUsesCreatesDto(): void
    {
        $traits = class_uses(DtoFormRequest::class);

        $this->assertContains(CreatesDto::class, $traits);
    }

    public function testToDtoThrowsWhenNoDtoClassSpecified(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('No DTO class specified');

        $request = new CreatesDtoTestFormRequest();
        $request->toDto();
    }
}
