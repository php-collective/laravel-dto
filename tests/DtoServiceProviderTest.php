<?php

declare(strict_types=1);

namespace PhpCollective\LaravelDto\Test;

use Illuminate\Support\Collection;
use Orchestra\Testbench\TestCase;
use PhpCollective\Dto\Dto\Dto;
use PhpCollective\LaravelDto\DtoServiceProvider;
use PhpCollective\LaravelDto\GenerateDtoCommand;

class DtoServiceProviderTest extends TestCase
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

    public function testServiceProviderIsLoaded(): void
    {
        $this->assertTrue($this->app->providerIsLoaded(DtoServiceProvider::class));
    }

    public function testConfigIsMerged(): void
    {
        $config = $this->app['config']->get('dto');

        $this->assertIsArray($config);
        $this->assertArrayHasKey('config_path', $config);
        $this->assertArrayHasKey('output_path', $config);
        $this->assertArrayHasKey('namespace', $config);
    }

    public function testCommandIsRegistered(): void
    {
        $this->assertTrue($this->app->bound(GenerateDtoCommand::class));
    }

    public function testCollectionFactoryIsConfigured(): void
    {
        // After boot, the collection factory should be set to Laravel's collect()
        $items = ['a', 'b', 'c'];
        $collection = Dto::getCollectionFactory()($items);

        $this->assertInstanceOf(Collection::class, $collection);
        $this->assertSame($items, $collection->toArray());
    }
}
