<?php

declare(strict_types=1);

namespace PhpCollective\LaravelDto\Test\Fixtures;

use PhpCollective\LaravelDto\Http\DtoFormRequest;

class CreatesDtoTestFormRequest extends DtoFormRequest
{
    /**
     * @return array<string, string>
     */
    public function rules(): array
    {
        return [];
    }
}
