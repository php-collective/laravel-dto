<?php

declare(strict_types=1);

namespace PhpCollective\LaravelDto\Test;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Schema;
use Orchestra\Testbench\TestCase;
use PhpCollective\LaravelDto\DtoServiceProvider;
use PhpCollective\LaravelDto\Eloquent\DtoMapper;
use PhpCollective\LaravelDto\Test\Fixtures\Models\BlogPost;
use PhpCollective\LaravelDto\Test\Fixtures\Models\BlogUser;
use PhpCollective\LaravelDto\Test\Fixtures\TestDto;

class DtoMapperTest extends TestCase
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

        Schema::create('blog_users', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
        });

        Schema::create('blog_posts', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->string('title');
        });
    }

    protected function tearDown(): void
    {
        Schema::drop('blog_posts');
        Schema::drop('blog_users');

        parent::tearDown();
    }

    public function testFromModelLoadsRelations(): void
    {
        $user = BlogUser::create(['name' => 'Mark']);
        BlogPost::create(['user_id' => $user->id, 'title' => 'First']);

        $user = BlogUser::query()->firstOrFail();

        $dto = DtoMapper::fromModel($user, TestDto::class, relations: ['posts']);

        $this->assertSame('First', $dto->data['posts'][0]['title']);
    }

    public function testFromCollectionMapsModels(): void
    {
        BlogUser::create(['name' => 'Alice']);
        BlogUser::create(['name' => 'Bob']);

        $dtos = DtoMapper::fromCollection(BlogUser::query()->get(), TestDto::class);

        $this->assertCount(2, $dtos);
        $this->assertSame('Alice', $dtos->first()->data['name']);
    }

    public function testFromPaginatorMapsItems(): void
    {
        BlogUser::create(['name' => 'Mark']);
        $users = BlogUser::query()->get();

        $paginator = new LengthAwarePaginator($users, $users->count(), 1);

        $mapped = DtoMapper::fromPaginator($paginator, TestDto::class);

        $this->assertInstanceOf(TestDto::class, $mapped->getCollection()->first());
    }
}
