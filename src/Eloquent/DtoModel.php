<?php

declare(strict_types=1);

namespace PhpCollective\LaravelDto\Eloquent;

use Illuminate\Database\Eloquent\Model;

abstract class DtoModel extends Model
{
    use CreatesDtoFromModel;
}
