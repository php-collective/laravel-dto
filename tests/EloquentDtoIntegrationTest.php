<?php

declare(strict_types=1);

namespace PhpCollective\LaravelDto\Test;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;
use Orchestra\Testbench\TestCase;
use PhpCollective\LaravelDto\DtoServiceProvider;
use PhpCollective\LaravelDto\Test\Fixtures\Models\AutoUser;
use PhpCollective\LaravelDto\Test\Fixtures\Models\User;
use PhpCollective\LaravelDto\Test\Fixtures\TestDto;

class EloquentDtoIntegrationTest extends TestCase
{
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

    /**
     * @param \Illuminate\Foundation\Application $app
     */
    protected function defineEnvironment($app): void
    {
        $app['config']->set('database.default', 'testing');
        $app['config']->set('database.connections.testing', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);
    }

    protected function setUp(): void
    {
        parent::setUp();

        Schema::create('users', function (Blueprint $table): void {
            $table->id();
            $table->json('profile')->nullable();
            $table->json('tags')->nullable();
        });
    }

    protected function tearDown(): void
    {
        Schema::drop('users');

        parent::tearDown();
    }

    public function testDtoCastHydratesFromArray(): void
    {
        User::create(['profile' => ['name' => 'Mark']]);

        $user = User::query()->firstOrFail();

        $this->assertInstanceOf(TestDto::class, $user->profile);
        $this->assertSame(['name' => 'Mark'], $user->profile->data);
    }

    public function testDtoCastPersistsDto(): void
    {
        $user = new User();
        $user->profile = new TestDto(['name' => 'Eva']);
        $user->save();

        $user = User::query()->firstOrFail();

        $this->assertInstanceOf(TestDto::class, $user->profile);
        $this->assertSame(['name' => 'Eva'], $user->profile->data);
    }

    public function testDtoCollectionCastHydratesCollection(): void
    {
        User::create([
            'tags' => [
                ['name' => 'alpha'],
                ['name' => 'beta'],
            ],
        ]);

        $user = User::query()->firstOrFail();

        $this->assertInstanceOf(Collection::class, $user->tags);
        $this->assertCount(2, $user->tags);
        $this->assertSame(['name' => 'alpha'], $user->tags->first()->data);
    }

    public function testModelToDtoUsesDefaultClass(): void
    {
        $user = User::create([
            'profile' => ['name' => 'Mark'],
            'tags' => [['name' => 'alpha']],
        ]);

        $dto = $user->toDto();

        $this->assertInstanceOf(TestDto::class, $dto);
        $this->assertSame(['name' => 'Mark'], $dto->data['profile']);
        $this->assertSame([['name' => 'alpha']], $dto->data['tags']);
        $this->assertSame($user->id, $dto->data['id']);
    }

    public function testAutoDtoCastsMergeIntoModelCasts(): void
    {
        AutoUser::create([
            'profile' => ['name' => 'Auto'],
            'tags' => [['name' => 'tag']],
        ]);

        $user = AutoUser::query()->firstOrFail();

        $this->assertInstanceOf(TestDto::class, $user->profile);
        $this->assertSame(['name' => 'Auto'], $user->profile->data);
        $this->assertSame(['name' => 'tag'], $user->tags->first()->data);
    }
}
