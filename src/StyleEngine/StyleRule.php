<?php

declare(strict_types=1);

namespace Folio\Pdf\StyleEngine;

final readonly class StyleRule
{
    /**
     * @param array<string, mixed> $properties
     * @param array<string, mixed> $conditions
     */
    public function __construct(
        public string $selector,
        public array $properties = [],
        public array $conditions = [],
    ) {
    }
}
