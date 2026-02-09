<?php

declare(strict_types=1);

namespace PhpCollective\LaravelDto\Test\Fixtures;

class ValidationTestDto extends TestDto
{
    /**
     * @var array<string, array<string, mixed>>
     */
    protected array $validationRules = [];

    /**
     * @param array<string, array<string, mixed>> $validationRules
     */
    public function __construct(array $validationRules = [])
    {
        parent::__construct();
        $this->validationRules = $validationRules;
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    public function validationRules(): array
    {
        return $this->validationRules;
    }
}
