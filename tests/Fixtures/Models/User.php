<?php

declare(strict_types=1);

namespace PhpCollective\LaravelDto\Test\Fixtures\Models;

use Illuminate\Database\Eloquent\Model;
use PhpCollective\LaravelDto\Eloquent\CreatesDtoFromModel;
use PhpCollective\LaravelDto\Eloquent\DtoCast;
use PhpCollective\LaravelDto\Eloquent\DtoCollectionCast;
use PhpCollective\LaravelDto\Test\Fixtures\TestDto;

class User extends Model
{
    use CreatesDtoFromModel;

    /**
     * @var string
     */
    protected $table = 'users';

    /**
     * @var bool
     */
    public $timestamps = false;

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'profile' => DtoCast::class . ':' . TestDto::class,
        'tags' => DtoCollectionCast::class . ':' . TestDto::class,
    ];

    /**
     * @var array<int, string>
     */
    protected $guarded = [];

    /**
     * @return class-string<\PhpCollective\Dto\Dto\AbstractDto>|null
     */
    protected function getDtoClass(): ?string
    {
        return TestDto::class;
    }
}
