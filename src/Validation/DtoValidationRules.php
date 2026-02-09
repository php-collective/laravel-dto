<?php

declare(strict_types=1);

namespace PhpCollective\LaravelDto\Validation;

use PhpCollective\Dto\Dto\Dto;

class DtoValidationRules
{
    /**
     * Convert DTO validation rules to Laravel validation rule arrays.
     *
     * @param \PhpCollective\Dto\Dto\Dto $dto
     *
     * @return array<string, array<string>>
     */
    public static function fromDto(Dto $dto): array
    {
        $laravelRules = [];
        foreach ($dto->validationRules() as $field => $rules) {
            $fieldRules = [];
            if (!empty($rules['required'])) {
                $fieldRules[] = 'required';
            }
            if (isset($rules['minLength'])) {
                $fieldRules[] = 'min:' . $rules['minLength'];
            }
            if (isset($rules['maxLength'])) {
                $fieldRules[] = 'max:' . $rules['maxLength'];
            }
            if (isset($rules['min'])) {
                $fieldRules[] = 'gte:' . $rules['min'];
            }
            if (isset($rules['max'])) {
                $fieldRules[] = 'lte:' . $rules['max'];
            }
            if (isset($rules['pattern'])) {
                $fieldRules[] = 'regex:' . $rules['pattern'];
            }
            if ($fieldRules) {
                $laravelRules[$field] = $fieldRules;
            }
        }

        return $laravelRules;
    }
}
