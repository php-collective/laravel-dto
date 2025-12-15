<?php

declare(strict_types=1);

namespace PhpCollective\LaravelDto;

use Illuminate\Console\Command;
use PhpCollective\Dto\Generator\IoInterface;

class LaravelConsoleIo implements IoInterface
{
    public function __construct(
        private Command $command,
    ) {
    }

    public function out(string $message): void
    {
        $this->command->line($message);
    }

    public function success(string $message): void
    {
        $this->command->info($message);
    }

    public function warning(string $message): void
    {
        $this->command->warn($message);
    }

    public function error(string $message): void
    {
        $this->command->error($message);
    }
}
