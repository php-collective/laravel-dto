<?php

declare(strict_types=1);

namespace PhpCollective\LaravelDto;

use Illuminate\Support\ServiceProvider;
use PhpCollective\Dto\Dto\Dto;

class DtoServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        // Auto-configure Laravel collections for DTO collection fields
        Dto::setCollectionFactory(fn (array $items) => collect($items));

        if ($this->app->runningInConsole()) {
            $this->commands([
                GenerateDtoCommand::class,
            ]);

            $this->publishes([
                __DIR__ . '/../config/dto.php' => config_path('dto.php'),
            ], 'dto-config');
        }
    }

    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/dto.php', 'dto');
    }
}
