<?php

declare(strict_types=1);

namespace PhpCollective\LaravelDto\Test;

use Illuminate\Support\Facades\Artisan;
use Orchestra\Testbench\TestCase;
use PhpCollective\LaravelDto\DtoServiceProvider;

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
        $this->assertArrayHasKey('typescript_output_path', $config);
        $this->assertArrayHasKey('jsonschema_output_path', $config);
    }

    public function testCommandsAreRegistered(): void
    {
        $commands = Artisan::all();

        $this->assertArrayHasKey('dto:generate', $commands);
        $this->assertArrayHasKey('dto:init', $commands);
        $this->assertArrayHasKey('dto:typescript', $commands);
        $this->assertArrayHasKey('dto:jsonschema', $commands);
    }
}
