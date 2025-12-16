<?php

declare(strict_types=1);

namespace PhpCollective\LaravelDto\Test;

use Orchestra\Testbench\TestCase;
use PhpCollective\LaravelDto\DtoServiceProvider;

class InitDtoCommandTest extends TestCase
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
        $this->tempDir = sys_get_temp_dir() . '/laravel_dto_init_test_' . getmypid();
        $this->removeDirectory($this->tempDir);
        mkdir($this->tempDir, 0777, true);

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
        $tempDir = sys_get_temp_dir() . '/laravel_dto_init_test_' . getmypid();
        $app['config']->set('dto.config_path', $tempDir);
    }

    public function testInitCreatesPhpConfig(): void
    {
        $this->artisan('dto:init')
            ->assertSuccessful();

        $this->assertFileExists($this->tempDir . '/dtos.php');

        $content = file_get_contents($this->tempDir . '/dtos.php');
        $this->assertStringContainsString('return [', $content);
        $this->assertStringContainsString("'name' => 'User'", $content);
    }

    public function testInitCreatesXmlConfig(): void
    {
        $this->artisan('dto:init', ['--format' => 'xml'])
            ->assertSuccessful();

        $this->assertFileExists($this->tempDir . '/dto.xml');

        $content = file_get_contents($this->tempDir . '/dto.xml');
        $this->assertStringContainsString('<?xml version="1.0"', $content);
        $this->assertStringContainsString('<dto name="User">', $content);
    }

    public function testInitCreatesYamlConfig(): void
    {
        $this->artisan('dto:init', ['--format' => 'yaml'])
            ->assertSuccessful();

        $this->assertFileExists($this->tempDir . '/dto.yaml');

        $content = file_get_contents($this->tempDir . '/dto.yaml');
        $this->assertStringContainsString('User:', $content);
        $this->assertStringContainsString('fields:', $content);
    }

    public function testInitFailsWhenFileExists(): void
    {
        file_put_contents($this->tempDir . '/dtos.php', '<?php return [];');

        $this->artisan('dto:init')
            ->assertFailed()
            ->expectsOutputToContain('Config file already exists');
    }

    public function testInitOverwritesWithForce(): void
    {
        file_put_contents($this->tempDir . '/dtos.php', '<?php return [];');

        $this->artisan('dto:init', ['--force' => true])
            ->assertSuccessful();

        $content = file_get_contents($this->tempDir . '/dtos.php');
        $this->assertStringContainsString("'name' => 'User'", $content);
    }

    public function testInitFailsWithInvalidFormat(): void
    {
        $this->artisan('dto:init', ['--format' => 'invalid'])
            ->assertFailed()
            ->expectsOutputToContain('Invalid format');
    }
}
