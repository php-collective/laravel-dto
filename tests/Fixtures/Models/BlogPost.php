<?php

declare(strict_types=1);

namespace PhpCollective\LaravelDto\Test\Fixtures\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BlogPost extends Model
{
    /**
     * @var string
     */
    protected $table = 'blog_posts';

    /**
     * @var bool
     */
    public $timestamps = false;

    /**
     * @var array<int, string>
     */
    protected $guarded = [];

    public function user(): BelongsTo
    {
        return $this->belongsTo(BlogUser::class, 'user_id');
    }
}
