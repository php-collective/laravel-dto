<?php

declare(strict_types=1);

namespace PhpCollective\LaravelDto\Http;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Http\Resources\Json\JsonResource;
use JsonSerializable;
use PhpCollective\Dto\Dto\AbstractDto;

class DtoResource extends JsonResource
{
    /**
     * @param mixed $request
     *
     * @return array<string, mixed>
     */
    public function toArray($request): array
    {
        if ($this->resource instanceof AbstractDto) {
            return $this->resource->toArray();
        }

        if ($this->resource instanceof Arrayable) {
            return $this->resource->toArray();
        }

        if (is_array($this->resource)) {
            return $this->resource;
        }

        $data = parent::toArray($request);

        if ($data instanceof Arrayable) {
            return $data->toArray();
        }

        if ($data instanceof JsonSerializable) {
            $data = $data->jsonSerialize();
        }

        return is_array($data) ? $data : (array)$data;
    }
}
