<?php

declare(strict_types=1);

namespace PhpCollective\LaravelDto;

use Illuminate\Console\Command;
use PhpCollective\Dto\Engine\PhpEngine;
use PhpCollective\Dto\Engine\XmlEngine;
use PhpCollective\Dto\Engine\YamlEngine;
use PhpCollective\Dto\Generator\ArrayConfig;
use PhpCollective\Dto\Generator\Builder;
use PhpCollective\Dto\Generator\Generator;
use PhpCollective\Dto\Generator\TwigRenderer;

class GenerateDtoCommand extends Command
{
    /**
     * @var string
     */
    protected $signature = 'dto:generate
        {--dry-run : Preview changes without writing files}
        {--f|force : Force regeneration of all DTOs}
        {--config-path= : Path to DTO config files}
        {--output-path= : Path for generated DTOs}
        {--namespace= : Namespace for generated DTOs}';

    /**
     * @var string
     */
    protected $description = 'Generate DTO classes from configuration';

    public function handle(): int
    {
        $configPath = $this->option('config-path') ?? config('dto.config_path', config_path());
        $outputPath = $this->option('output-path') ?? config('dto.output_path', app_path('Dto'));
        $namespace = $this->option('namespace') ?? config('dto.namespace', 'App\\Dto');

        // Ensure paths end with /
        if (!str_ends_with($configPath, '/')) {
            $configPath .= '/';
        }
        if (!str_ends_with($outputPath, '/')) {
            $outputPath .= '/';
        }

        // Validate config path exists
        if (!is_dir($configPath)) {
            $this->error("Config path does not exist: {$configPath}");

            return self::FAILURE;
        }

        // Check if any config files exist
        $engine = $this->detectEngine($configPath);
        if ($engine === null) {
            $this->error("No DTO configuration files found in: {$configPath}");
            $this->line('');
            $this->line('Expected one of:');
            $this->line('  - dto.php, dto.xml, dto.yml, dto.yaml');
            $this->line('  - dto/ subdirectory with config files');
            $this->line('');
            $this->line('Run "php artisan dto:init" to create a starter configuration.');

            return self::FAILURE;
        }

        $config = new ArrayConfig([
            'namespace' => $namespace,
            'dryRun' => $this->option('dry-run'),
            'verbose' => $this->getOutput()->isVerbose(),
        ]);

        $builder = new Builder($engine, $config);
        $renderer = new TwigRenderer(null, $config);
        $io = new LaravelConsoleIo($this);

        $generator = new Generator($builder, $renderer, $io, $config);
        $result = $generator->generate($configPath, $outputPath, [
            'dryRun' => $this->option('dry-run'),
            'verbose' => $this->getOutput()->isVerbose(),
            'force' => $this->option('force'),
        ]);

        if ($this->option('dry-run')) {
            $this->info('Dry run complete. No files were written.');
        } else {
            $this->info('DTOs generated successfully.');
        }

        return self::SUCCESS;
    }

    private function detectEngine(string $configPath): PhpEngine|XmlEngine|YamlEngine|null
    {
        $sep = str_ends_with($configPath, '/') ? '' : '/';

        // Standard dto.* naming (preferred)
        if (file_exists($configPath . $sep . 'dto.php')) {
            return new PhpEngine();
        }
        if (file_exists($configPath . $sep . 'dto.xml')) {
            return new XmlEngine();
        }
        if (file_exists($configPath . $sep . 'dto.yml') || file_exists($configPath . $sep . 'dto.yaml')) {
            return new YamlEngine();
        }

        // Legacy dtos.* naming (backward compatibility)
        if (file_exists($configPath . $sep . 'dtos.php')) {
            return new PhpEngine();
        }
        if (file_exists($configPath . $sep . 'dtos.xml')) {
            return new XmlEngine();
        }
        if (file_exists($configPath . $sep . 'dtos.yml') || file_exists($configPath . $sep . 'dtos.yaml')) {
            return new YamlEngine();
        }

        // Check for dto/ subdirectory
        if (is_dir($configPath . $sep . 'dto')) {
            $dtoDir = $configPath . $sep . 'dto/';
            if (glob($dtoDir . '*.php')) {
                return new PhpEngine();
            }
            if (glob($dtoDir . '*.xml')) {
                return new XmlEngine();
            }
            if (glob($dtoDir . '*.yml') || glob($dtoDir . '*.yaml')) {
                return new YamlEngine();
            }
        }

        return null;
    }
}
