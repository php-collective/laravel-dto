<?php

declare(strict_types=1);

namespace PhpCollective\LaravelDto\Test\Fixtures\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BlogUser extends Model
{
    /**
     * @var string
     */
    protected $table = 'blog_users';

    /**
     * @var bool
     */
    public $timestamps = false;

    /**
     * @var array<int, string>
     */
    protected $guarded = [];

    public function posts(): HasMany
    {
        return $this->hasMany(BlogPost::class, 'user_id');
    }
}
