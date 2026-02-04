<?php

declare(strict_types=1);

namespace PhpCollective\LaravelDto;

use Illuminate\Console\Command;
use PhpCollective\Dto\Engine\PhpEngine;
use PhpCollective\Dto\Engine\XmlEngine;
use PhpCollective\Dto\Engine\YamlEngine;
use PhpCollective\Dto\Generator\ArrayConfig;
use PhpCollective\Dto\Generator\Builder;
use PhpCollective\Dto\Generator\TypeScriptGenerator;

class TypeScriptDtoCommand extends Command
{
    /**
     * @var string
     */
    protected $signature = 'dto:typescript
        {--config-path= : Path to DTO config files}
        {--output-path= : Path for generated TypeScript files}
        {--namespace= : Namespace for DTOs}
        {--single-file : Generate all interfaces in a single file (default)}
        {--multiple-files : Generate each interface in its own file}
        {--readonly : Make all properties readonly}
        {--strict-nulls : Use explicit null union types instead of optional properties}
        {--export-style=interface : Export style: interface or type}
        {--date-type=string : Date type: string or Date}
        {--suffix=Dto : Suffix for interface names}
        {--file-name-case=pascal : File name case: pascal, dashed, or snake}';

    /**
     * @var string
     */
    protected $description = 'Generate TypeScript interfaces from DTO configuration';

    public function handle(): int
    {
        $configPath = $this->option('config-path') ?? config('dto.config_path', config_path());
        $outputPath = $this->option('output-path') ?? config('dto.typescript_output_path', resource_path('js/types'));
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
            $this->line('  - dtos.php, dtos.xml, dtos.yml, dtos.yaml');
            $this->line('  - dto.xml, dto.yml, dto.yaml');
            $this->line('  - dto/ subdirectory with config files');
            $this->line('');
            $this->line('Run "php artisan dto:init" to create a starter configuration.');

            return self::FAILURE;
        }

        $config = new ArrayConfig([
            'namespace' => $namespace,
        ]);

        $builder = new Builder($engine, $config);
        $definitions = $builder->build($configPath, [
            'namespace' => str_replace('\\Dto', '', $namespace),
        ]);

        // Transform definitions to the format expected by TypeScriptGenerator
        $transformedDefinitions = $this->transformDefinitions($definitions);

        $io = new LaravelConsoleIo($this);

        $options = [
            'singleFile' => !$this->option('multiple-files'),
            'readonly' => (bool)$this->option('readonly'),
            'strictNulls' => (bool)$this->option('strict-nulls'),
            'exportStyle' => $this->option('export-style'),
            'dateType' => $this->option('date-type'),
            'suffix' => $this->option('suffix'),
            'fileNameCase' => $this->option('file-name-case'),
        ];

        $generator = new TypeScriptGenerator($io, $options);
        $count = $generator->generate($transformedDefinitions, $outputPath);

        $this->info("Generated {$count} TypeScript file(s) in {$outputPath}");

        return self::SUCCESS;
    }

    /**
     * Transform Builder definitions to the format expected by TypeScriptGenerator.
     *
     * @param array<string, array<string, mixed>> $definitions
     *
     * @return array<string, array<string, mixed>>
     */
    private function transformDefinitions(array $definitions): array
    {
        $result = [];

        foreach ($definitions as $name => $dto) {
            $fields = [];

            foreach ($dto['fields'] ?? [] as $fieldName => $field) {
                $fields[$fieldName] = [
                    'type' => $field['type'] ?? 'mixed',
                    'required' => $field['required'] ?? false,
                    'nullable' => $field['nullable'] ?? true,
                    'collection' => $field['collection'] ?? false,
                    'singular' => $field['singular'] ?? null,
                    'singularClass' => $field['singularClass'] ?? null,
                    'dto' => $field['dto'] ?? null,
                ];
            }

            $result[$name] = [
                'fields' => $fields,
                'immutable' => $dto['immutable'] ?? false,
            ];
        }

        return $result;
    }

    private function detectEngine(string $configPath): PhpEngine|XmlEngine|YamlEngine|null
    {
        $sep = str_ends_with($configPath, '/') ? '' : '/';

        // Check for dtos.* files first (recommended to avoid Laravel config conflict)
        if (file_exists($configPath . $sep . 'dtos.php')) {
            return new PhpEngine();
        }
        if (file_exists($configPath . $sep . 'dtos.xml')) {
            return new XmlEngine();
        }
        if (file_exists($configPath . $sep . 'dtos.yml') || file_exists($configPath . $sep . 'dtos.yaml')) {
            return new YamlEngine();
        }

        // Fall back to dto.* files (XML/YAML only - dto.php conflicts with Laravel config)
        if (file_exists($configPath . $sep . 'dto.xml')) {
            return new XmlEngine();
        }
        if (file_exists($configPath . $sep . 'dto.yml') || file_exists($configPath . $sep . 'dto.yaml')) {
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
