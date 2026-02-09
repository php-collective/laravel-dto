<?php

declare(strict_types=1);

namespace PhpCollective\LaravelDto\Test\Fixtures\Models;

use Illuminate\Database\Eloquent\Model;
use PhpCollective\LaravelDto\Eloquent\HasDtoCasts;
use PhpCollective\LaravelDto\Test\Fixtures\TestDto;

class AutoUser extends Model
{
    use HasDtoCasts;

    /**
     * @var string
     */
    protected $table = 'users';

    /**
     * @var bool
     */
    public $timestamps = false;

    /**
     * @var array<int, string>
     */
    protected $guarded = [];

    /**
     * @var array<string, class-string<\PhpCollective\Dto\Dto\AbstractDto>|array{class: class-string<\PhpCollective\Dto\Dto\AbstractDto>, collection?: bool}>
     */
    protected array $dtoCasts = [
        'profile' => TestDto::class,
        'tags' => [
            'class' => TestDto::class,
            'collection' => true,
        ],
    ];
}
