<?php

declare(strict_types=1);

namespace Folio\Pdf\StyleEngine;

final class Recipe
{
    /** @var array<string, mixed> */
    private readonly array $base;

    /** @var array<string, array<string, array<string, mixed>>> */
    private readonly array $variants;

    /** @var array<string, string> */
    private readonly array $defaultVariants;

    /**
     * @param array<string, mixed> $base
     * @param array<string, array<string, array<string, mixed>>> $variants
     * @param array<string, string> $defaultVariants
     */
    public function __construct(
        private readonly string $name,
        array $base = [],
        array $variants = [],
        array $defaultVariants = [],
    ) {
        $this->base = $base;
        $this->variants = $variants;
        $this->defaultVariants = $defaultVariants;
    }

    public function name(): string
    {
        return $this->name;
    }

    /**
     * @param array<string, string> $selectedVariants
     *
     * @return array<string, mixed>
     */
    public function resolve(array $selectedVariants = []): array
    {
        $properties = $this->base;

        $active = array_merge($this->defaultVariants, $selectedVariants);

        foreach ($active as $group => $option) {
            $variant = $this->variants[$group][$option] ?? null;

            if ($variant !== null) {
                $properties = array_merge($properties, $variant);
            }
        }

        return $properties;
    }

    /**
     * @return array<string, array<string, array<string, mixed>>>
     */
    public function variants(): array
    {
        return $this->variants;
    }
}
