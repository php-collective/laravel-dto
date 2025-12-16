<?php

declare(strict_types=1);

namespace PhpCollective\LaravelDto;

use Illuminate\Console\Command;

class InitDtoCommand extends Command
{
    /**
     * @var string
     */
    protected $signature = 'dto:init
        {--format=php : Config format (php, xml, yaml)}
        {--force : Overwrite existing config file}';

    /**
     * @var string
     */
    protected $description = 'Create a starter DTO configuration file';

    public function handle(): int
    {
        $format = strtolower($this->option('format'));
        $configPath = config('dto.config_path', config_path());

        if (!in_array($format, ['php', 'xml', 'yaml', 'yml'], true)) {
            $this->error("Invalid format: {$format}. Use php, xml, or yaml.");

            return self::FAILURE;
        }

        if ($format === 'yml') {
            $format = 'yaml';
        }

        $filename = $format === 'php' ? 'dtos.php' : 'dto.' . $format;
        $filePath = rtrim($configPath, '/') . '/' . $filename;

        if (file_exists($filePath) && !$this->option('force')) {
            $this->error("Config file already exists: {$filePath}");
            $this->line('Use --force to overwrite.');

            return self::FAILURE;
        }

        $content = $this->getConfigContent($format);

        if (!is_dir(dirname($filePath))) {
            mkdir(dirname($filePath), 0755, true);
        }

        file_put_contents($filePath, $content);

        $this->info("Created DTO configuration: {$filePath}");
        $this->line('');
        $this->line('Next steps:');
        $this->line('  1. Edit the configuration to define your DTOs');
        $this->line('  2. Run "php artisan dto:generate" to generate the DTO classes');

        return self::SUCCESS;
    }

    private function getConfigContent(string $format): string
    {
        return match ($format) {
            'php' => $this->getPhpConfig(),
            'xml' => $this->getXmlConfig(),
            'yaml' => $this->getYamlConfig(),
        };
    }

    private function getPhpConfig(): string
    {
        return <<<'PHP'
<?php

/**
 * DTO Configuration
 *
 * Define your Data Transfer Objects here.
 *
 * @see https://github.com/php-collective/dto
 */
return [
    // Example DTO definition
    [
        'name' => 'User',
        'fields' => [
            [
                'name' => 'id',
                'type' => 'int',
            ],
            [
                'name' => 'email',
                'type' => 'string',
            ],
            [
                'name' => 'name',
                'type' => 'string',
                'nullable' => true,
            ],
            [
                'name' => 'createdAt',
                'type' => '\DateTimeInterface',
                'nullable' => true,
            ],
        ],
    ],

    // Add more DTOs here...
];
PHP;
    }

    private function getXmlConfig(): string
    {
        return <<<'XML'
<?xml version="1.0" encoding="UTF-8"?>
<!--
    DTO Configuration

    Define your Data Transfer Objects here.

    @see https://github.com/php-collective/dto
-->
<dtos xmlns="php-collective-dto">
    <!-- Example DTO definition -->
    <dto name="User">
        <field name="id" type="int"/>
        <field name="email" type="string"/>
        <field name="name" type="string" nullable="true"/>
        <field name="createdAt" type="\DateTimeInterface" nullable="true"/>
    </dto>

    <!-- Add more DTOs here... -->
</dtos>
XML;
    }

    private function getYamlConfig(): string
    {
        return <<<'YAML'
# DTO Configuration
#
# Define your Data Transfer Objects here.
#
# @see https://github.com/php-collective/dto

# Example DTO definition
User:
  fields:
    id:
      type: int
    email:
      type: string
    name:
      type: string
      nullable: true
    createdAt:
      type: \DateTimeInterface
      nullable: true

# Add more DTOs here...
YAML;
    }
}
