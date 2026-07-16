<?php

declare(strict_types=1);

namespace Folio\Pdf\StyleEngine;

final class SlotRecipe
{
    /** @var array<string, mixed> */
    private readonly array $base;

    /** @var array<string, array<string, mixed>> */
    private readonly array $slots;

    /** @var array<string, array<string, array<string, mixed>>> */
    private readonly array $variants;

    /**
     * @param array<string, mixed> $base
     * @param array<string, array<string, mixed>> $slots
     * @param array<string, array<string, array<string, mixed>>> $variants
     */
    public function __construct(
        private readonly string $name,
        array $base = [],
        array $slots = [],
        array $variants = [],
    ) {
        $this->base = $base;
        $this->slots = $slots;
        $this->variants = $variants;
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
    public function resolve(string $slot, array $selectedVariants = []): array
    {
        $properties = array_merge($this->base, $this->slots[$slot] ?? []);

        $active = $selectedVariants;

        foreach ($active as $group => $option) {
            $variant = $this->variants[$group][$option] ?? null;

            if ($variant !== null) {
                $properties = array_merge($properties, $variant);
            }
        }

        return $properties;
    }
}
