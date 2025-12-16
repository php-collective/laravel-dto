<?php

declare(strict_types=1);

namespace PhpCollective\LaravelDto\Test;

use Illuminate\Console\Command;
use PhpCollective\Dto\Generator\IoInterface;
use PhpCollective\LaravelDto\LaravelConsoleIo;
use PHPUnit\Framework\TestCase;

class LaravelConsoleIoTest extends TestCase
{
    public function testImplementsIoInterface(): void
    {
        $command = $this->createMock(Command::class);
        $io = new LaravelConsoleIo($command);

        $this->assertInstanceOf(IoInterface::class, $io);
    }

    public function testVerboseWithString(): void
    {
        $command = $this->createMock(Command::class);
        $command->expects($this->once())
            ->method('line')
            ->with('test message', null, 'v');

        $io = new LaravelConsoleIo($command);
        $result = $io->verbose('test message');

        $this->assertNull($result);
    }

    public function testVerboseWithArray(): void
    {
        $command = $this->createMock(Command::class);
        $command->expects($this->once())
            ->method('line')
            ->with("line1\nline2", null, 'v');

        $io = new LaravelConsoleIo($command);
        $io->verbose(['line1', 'line2']);
    }

    public function testQuietWithString(): void
    {
        $command = $this->createMock(Command::class);
        $command->expects($this->once())
            ->method('line')
            ->with('quiet message', null, 'quiet');

        $io = new LaravelConsoleIo($command);
        $result = $io->quiet('quiet message');

        $this->assertNull($result);
    }

    public function testOutWithMessage(): void
    {
        $command = $this->createMock(Command::class);
        $command->expects($this->once())
            ->method('line')
            ->with('output message');

        $io = new LaravelConsoleIo($command);
        $result = $io->out('output message');

        $this->assertNull($result);
    }

    public function testOutWithNull(): void
    {
        $command = $this->createMock(Command::class);
        $command->expects($this->never())
            ->method('line');

        $io = new LaravelConsoleIo($command);
        $result = $io->out(null);

        $this->assertNull($result);
    }

    public function testErrorWithMessage(): void
    {
        $command = $this->createMock(Command::class);
        $command->expects($this->once())
            ->method('error')
            ->with('error message');

        $io = new LaravelConsoleIo($command);
        $result = $io->error('error message');

        $this->assertNull($result);
    }

    public function testErrorWithNull(): void
    {
        $command = $this->createMock(Command::class);
        $command->expects($this->never())
            ->method('error');

        $io = new LaravelConsoleIo($command);
        $result = $io->error(null);

        $this->assertNull($result);
    }

    public function testSuccessWithMessage(): void
    {
        $command = $this->createMock(Command::class);
        $command->expects($this->once())
            ->method('info')
            ->with('success message');

        $io = new LaravelConsoleIo($command);
        $result = $io->success('success message');

        $this->assertNull($result);
    }

    public function testSuccessWithNull(): void
    {
        $command = $this->createMock(Command::class);
        $command->expects($this->never())
            ->method('info');

        $io = new LaravelConsoleIo($command);
        $result = $io->success(null);

        $this->assertNull($result);
    }
}
