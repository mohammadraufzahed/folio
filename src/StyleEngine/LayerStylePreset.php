<?php

declare(strict_types=1);

namespace Folio\Pdf\StyleEngine;

final readonly class LayerStylePreset
{
    /**
     * @param array<string, mixed> $properties
     */
    public function __construct(
        public string $name,
        public array $properties = [],
    ) {
    }
}
