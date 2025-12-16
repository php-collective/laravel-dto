<?php

declare(strict_types=1);

namespace PhpCollective\LaravelDto\Test;

use Illuminate\Support\Facades\Artisan;
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
        $this->tempDir = sys_get_temp_dir() . '/laravel_dto_test_' . getmypid();
        $this->removeDirectory($this->tempDir);
        mkdir($this->tempDir, 0777, true);
        mkdir($this->tempDir . '/config', 0777, true);
        mkdir($this->tempDir . '/output', 0777, true);

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
        $app['config']->set('dto.output_path', $tempDir . '/output');
        $app['config']->set('dto.namespace', 'TestApp\\Dto');
    }

    public function testCommandExists(): void
    {
        $commands = Artisan::all();

        $this->assertArrayHasKey('dto:generate', $commands);
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
