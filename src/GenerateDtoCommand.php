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
    protected $signature = 'dto:generate
                            {--dry-run : Preview changes without writing files}
                            {--verbose : Show detailed output}';

    protected $description = 'Generate DTO classes from configuration';

    public function handle(): int
    {
        $configPath = config('dto.config_path', config_path());
        $outputPath = config('dto.output_path', app_path('Dto'));
        $namespace = config('dto.namespace', 'App\\Dto');

        $config = new ArrayConfig([
            'namespace' => $namespace,
            'dryRun' => $this->option('dry-run'),
            'verbose' => $this->option('verbose'),
        ]);

        $engine = $this->detectEngine($configPath);
        $builder = new Builder($engine, $config);
        $renderer = new TwigRenderer(null, $config);
        $io = new LaravelConsoleIo($this);

        $generator = new Generator($builder, $renderer, $io, $config);
        $generator->generate($configPath, $outputPath);

        $this->info('DTOs generated successfully.');

        return self::SUCCESS;
    }

    private function detectEngine(string $configPath): PhpEngine|XmlEngine|YamlEngine
    {
        if (file_exists($configPath . '/dto.php')) {
            return new PhpEngine();
        }
        if (file_exists($configPath . '/dto.xml')) {
            return new XmlEngine();
        }
        if (file_exists($configPath . '/dto.yml') || file_exists($configPath . '/dto.yaml')) {
            return new YamlEngine();
        }

        return new PhpEngine();
    }
}
