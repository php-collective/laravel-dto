<?php

declare(strict_types=1);

namespace PhpCollective\LaravelDto;

use Illuminate\Console\Command;
use PhpCollective\Dto\Generator\IoInterface;

class LaravelConsoleIo implements IoInterface
{
    public function __construct(private Command $command)
    {
    }

    /**
     * @inheritDoc
     */
    public function verbose(array|string $message, int $newlines = 1): ?int
    {
        if (is_array($message)) {
            $message = implode(PHP_EOL, $message);
        }
        $this->command->line($message, null, 'v');

        return null;
    }

    /**
     * @inheritDoc
     */
    public function quiet(array|string $message, int $newlines = 1): ?int
    {
        if (is_array($message)) {
            $message = implode(PHP_EOL, $message);
        }
        $this->command->line($message, null, 'quiet');

        return null;
    }

    /**
     * @inheritDoc
     */
    public function out(?string $message = null, int $newlines = 1, int $level = self::NORMAL): ?int
    {
        if ($message === null) {
            return null;
        }
        $this->command->line($message);

        return null;
    }

    /**
     * @inheritDoc
     */
    public function error(?string $message = null, int $newlines = 1): ?int
    {
        if ($message === null) {
            return null;
        }
        $this->command->error($message);

        return null;
    }

    /**
     * @inheritDoc
     */
    public function success(?string $message = null, int $newlines = 1, int $level = self::NORMAL): ?int
    {
        if ($message === null) {
            return null;
        }
        $this->command->info($message);

        return null;
    }

    /**
     * @inheritDoc
     */
    public function abort(string $message, int $exitCode = 1): void
    {
        $this->command->error($message);
        exit($exitCode);
    }
}
