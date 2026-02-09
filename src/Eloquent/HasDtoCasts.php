<?php

declare(strict_types=1);

namespace PhpCollective\LaravelDto\Eloquent;

use InvalidArgumentException;

trait HasDtoCasts
{
    protected function initializeHasDtoCasts(): void
    {
        $dtoCasts = $this->getDtoCasts();
        if ($dtoCasts === []) {
            return;
        }

        $this->mergeCasts($this->resolveDtoCasts($dtoCasts));
    }

    /**
     * @throws \InvalidArgumentException
     *
     * @return array<string, class-string<\PhpCollective\Dto\Dto\AbstractDto>|array{class: class-string<\PhpCollective\Dto\Dto\AbstractDto>, collection?: bool}>
     */
    protected function getDtoCasts(): array
    {
        if (!property_exists($this, 'dtoCasts')) {
            return [];
        }

        $dtoCasts = $this->dtoCasts;
        if (!is_array($dtoCasts)) {
            throw new InvalidArgumentException('Property $dtoCasts must be an array.');
        }

        return $dtoCasts;
    }

    /**
     * @param array<string, class-string<\PhpCollective\Dto\Dto\AbstractDto>|array{class: class-string<\PhpCollective\Dto\Dto\AbstractDto>, collection?: bool}> $dtoCasts
     *
     * @return array<string, string>
     */
    protected function resolveDtoCasts(array $dtoCasts): array
    {
        $casts = [];
        foreach ($dtoCasts as $attribute => $config) {
            $casts[$attribute] = $this->buildDtoCast($attribute, $config);
        }

        return $casts;
    }

    /**
     * @param string $attribute
     * @param class-string<\PhpCollective\Dto\Dto\AbstractDto>|array{class: class-string<\PhpCollective\Dto\Dto\AbstractDto>, collection?: bool} $config
     *
     * @throws \InvalidArgumentException
     */
    private function buildDtoCast(string $attribute, string|array $config): string
    {
        if (is_string($config)) {
            return DtoCast::class . ':' . $config;
        }

        if (!isset($config['class']) || !is_string($config['class'])) {
            throw new InvalidArgumentException("DTO cast for [{$attribute}] must define a DTO class.");
        }

        $castClass = ($config['collection'] ?? false) ? DtoCollectionCast::class : DtoCast::class;

        return $castClass . ':' . $config['class'];
    }
}
