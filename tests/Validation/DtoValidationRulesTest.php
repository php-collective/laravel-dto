<?php

declare(strict_types=1);

namespace PhpCollective\LaravelDto\Test\Validation;

use PhpCollective\LaravelDto\Test\Fixtures\ValidationTestDto;
use PhpCollective\LaravelDto\Validation\DtoValidationRules;
use PHPUnit\Framework\TestCase;

class DtoValidationRulesTest extends TestCase
{
    public function testRequiredRuleMapsCorrectly(): void
    {
        $dto = new ValidationTestDto([
            'name' => ['required' => true],
        ]);

        $result = DtoValidationRules::fromDto($dto);

        $this->assertSame(['required'], $result['name']);
    }

    public function testMinLengthMapsToMin(): void
    {
        $dto = new ValidationTestDto([
            'name' => ['minLength' => 3],
        ]);

        $result = DtoValidationRules::fromDto($dto);

        $this->assertSame(['min:3'], $result['name']);
    }

    public function testMaxLengthMapsToMax(): void
    {
        $dto = new ValidationTestDto([
            'name' => ['maxLength' => 255],
        ]);

        $result = DtoValidationRules::fromDto($dto);

        $this->assertSame(['max:255'], $result['name']);
    }

    public function testMinMapsToGte(): void
    {
        $dto = new ValidationTestDto([
            'age' => ['min' => 18],
        ]);

        $result = DtoValidationRules::fromDto($dto);

        $this->assertSame(['gte:18'], $result['age']);
    }

    public function testMaxMapsToLte(): void
    {
        $dto = new ValidationTestDto([
            'age' => ['max' => 120],
        ]);

        $result = DtoValidationRules::fromDto($dto);

        $this->assertSame(['lte:120'], $result['age']);
    }

    public function testPatternMapsToRegex(): void
    {
        $dto = new ValidationTestDto([
            'code' => ['pattern' => '/^[A-Z]{3}$/'],
        ]);

        $result = DtoValidationRules::fromDto($dto);

        $this->assertSame(['regex:/^[A-Z]{3}$/'], $result['code']);
    }

    public function testMultipleRulesCombine(): void
    {
        $dto = new ValidationTestDto([
            'name' => ['required' => true, 'minLength' => 2, 'maxLength' => 100],
            'age' => ['required' => true, 'min' => 0, 'max' => 150],
        ]);

        $result = DtoValidationRules::fromDto($dto);

        $this->assertSame(['required', 'min:2', 'max:100'], $result['name']);
        $this->assertSame(['required', 'gte:0', 'lte:150'], $result['age']);
    }

    public function testEmptyRulesReturnsEmptyArray(): void
    {
        $dto = new ValidationTestDto([]);

        $result = DtoValidationRules::fromDto($dto);

        $this->assertSame([], $result);
    }

    public function testFieldsWithNoMatchingRulesAreExcluded(): void
    {
        $dto = new ValidationTestDto([
            'name' => ['someUnknown' => true],
        ]);

        $result = DtoValidationRules::fromDto($dto);

        $this->assertSame([], $result);
    }
}
