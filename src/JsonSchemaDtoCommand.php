<?php

declare(strict_types=1);

namespace PhpCollective\LaravelDto;

use Illuminate\Console\Command;
use PhpCollective\Dto\Engine\PhpEngine;
use PhpCollective\Dto\Engine\XmlEngine;
use PhpCollective\Dto\Engine\YamlEngine;
use PhpCollective\Dto\Generator\ArrayConfig;
use PhpCollective\Dto\Generator\Builder;
use PhpCollective\Dto\Generator\JsonSchemaGenerator;

class JsonSchemaDtoCommand extends Command
{
    /**
     * @var string
     */
    protected $signature = 'dto:jsonschema
        {--config-path= : Path to DTO config files}
        {--output-path= : Path for generated JSON Schema files}
        {--namespace= : Namespace for DTOs}
        {--single-file : Generate all schemas in a single file with $defs (default)}
        {--multiple-files : Generate each schema in its own file}
        {--schema-version=https://json-schema.org/draft/2020-12/schema : JSON Schema version}
        {--date-format=date-time : Date format: date-time, date, or string}
        {--no-refs : Inline nested schemas instead of using $ref}
        {--suffix=Dto : Suffix for schema names}';

    /**
     * @var string
     */
    protected $description = 'Generate JSON Schema from DTO configuration';

    public function handle(): int
    {
        $configPath = $this->option('config-path') ?? config('dto.config_path', config_path());
        $outputPath = $this->option('output-path') ?? config('dto.jsonschema_output_path', resource_path('schemas'));
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
        ]);

        $builder = new Builder($engine, $config);
        $definitions = $builder->build($configPath, [
            'namespace' => str_replace('\\Dto', '', $namespace),
        ]);

        // Transform definitions to the format expected by JsonSchemaGenerator
        $transformedDefinitions = $this->transformDefinitions($definitions);

        $io = new LaravelConsoleIo($this);

        $options = [
            'singleFile' => !$this->option('multiple-files'),
            'schemaVersion' => $this->option('schema-version'),
            'dateFormat' => $this->option('date-format'),
            'useRefs' => !$this->option('no-refs'),
            'suffix' => $this->option('suffix'),
        ];

        $generator = new JsonSchemaGenerator($io, $options);
        $count = $generator->generate($transformedDefinitions, $outputPath);

        $this->info("Generated {$count} JSON Schema file(s) in {$outputPath}");

        return self::SUCCESS;
    }

    /**
     * Transform Builder definitions to the format expected by JsonSchemaGenerator.
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
                    'isArray' => $field['isArray'] ?? false,
                    'singular' => $field['singular'] ?? null,
                    'singularType' => $field['singularType'] ?? null,
                    'singularClass' => $field['singularClass'] ?? null,
                    'dto' => $field['dto'] ?? null,
                ];
            }

            $result[$name] = [
                'fields' => $fields,
                'immutable' => $dto['immutable'] ?? false,
                'description' => $dto['deprecated'] ?? null,
            ];
        }

        return $result;
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
