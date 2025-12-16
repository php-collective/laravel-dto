<?php

declare(strict_types=1);

namespace PhpCollective\LaravelDto\Test;

use Orchestra\Testbench\TestCase;
use PhpCollective\LaravelDto\DtoServiceProvider;

class GenerateDtoCommandTest extends TestCase
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
        parent::setUp();

        $this->tempDir = sys_get_temp_dir() . '/laravel_dto_test_' . uniqid();
        mkdir($this->tempDir, 0777, true);
        mkdir($this->tempDir . '/config', 0777, true);
        mkdir($this->tempDir . '/output', 0777, true);
    }

    protected function tearDown(): void
    {
        $this->removeDirectory($this->tempDir);
        parent::tearDown();
    }

    protected function removeDirectory(string $dir): void
    {
        if (!is_dir($dir)) {
            return;
        }
        $files = array_diff(scandir($dir), ['.', '..']);
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
        $app['config']->set('dto.config_path', $this->tempDir . '/config');
        $app['config']->set('dto.output_path', $this->tempDir . '/output');
        $app['config']->set('dto.namespace', 'TestApp\\Dto');
    }

    public function testCommandExists(): void
    {
        $this->assertTrue($this->app['artisan']->has('dto:generate'));
    }

    public function testCommandWithDryRun(): void
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

        $this->artisan('dto:generate', ['--dry-run' => true])
            ->assertSuccessful();

        // With dry-run, no files should be created
        $this->assertFileDoesNotExist($this->tempDir . '/output/Dto/UserDto.php');
    }

    public function testCommandGeneratesDto(): void
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

        $this->artisan('dto:generate')
            ->assertSuccessful();

        $this->assertFileExists($this->tempDir . '/output/Dto/UserDto.php');

        $content = file_get_contents($this->tempDir . '/output/Dto/UserDto.php');
        $this->assertStringContainsString('namespace TestApp\\Dto', $content);
        $this->assertStringContainsString('class UserDto', $content);
    }
}
