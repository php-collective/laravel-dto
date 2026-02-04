<?php

declare(strict_types=1);

namespace PhpCollective\LaravelDto\Test;

use Orchestra\Testbench\TestCase;
use PhpCollective\LaravelDto\DtoServiceProvider;

class TypeScriptDtoCommandTest extends TestCase
{
    protected string $tempDir;

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

    protected function setUp(): void
    {
        $this->tempDir = sys_get_temp_dir() . '/laravel_dto_test_' . getmypid();
        $this->removeDirectory($this->tempDir);
        mkdir($this->tempDir, 0777, true);
        mkdir($this->tempDir . '/config', 0777, true);
        mkdir($this->tempDir . '/types', 0777, true);

        parent::setUp();
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        $this->removeDirectory($this->tempDir);
    }

    protected function removeDirectory(string $dir): void
    {
        if (!is_dir($dir)) {
            return;
        }
        $files = array_diff((array)scandir($dir), ['.', '..']);
        foreach ($files as $file) {
            $path = $dir . '/' . $file;
            is_dir($path) ? $this->removeDirectory($path) : unlink($path);
        }
        rmdir($dir);
    }

    /**
     * @param \Illuminate\Foundation\Application $app
     */
    protected function defineEnvironment($app): void
    {
        $tempDir = sys_get_temp_dir() . '/laravel_dto_test_' . getmypid();
        $app['config']->set('dto.config_path', $tempDir . '/config');
        $app['config']->set('dto.typescript_output_path', $tempDir . '/types');
        $app['config']->set('dto.namespace', 'TestApp\\Dto');
    }

    public function testCommandGeneratesTypeScript(): void
    {
        $configContent = <<<'XML'
<?xml version="1.0" encoding="UTF-8"?>
<dtos xmlns="php-collective-dto">
    <dto name="User">
        <field name="id" type="int"/>
        <field name="name" type="string"/>
    </dto>
</dtos>
XML;
        file_put_contents($this->tempDir . '/config/dto.xml', $configContent);

        $this->artisan('dto:typescript')
            ->assertSuccessful();

        $this->assertFileExists($this->tempDir . '/types/dto.ts');
    }

    public function testCommandFailsWhenNoConfigFound(): void
    {
        $this->artisan('dto:typescript')
            ->assertFailed()
            ->expectsOutputToContain('No DTO configuration files found');
    }
}
