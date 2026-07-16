<?php

declare(strict_types=1);

namespace Folio\Pdf\StyleEngine;

final readonly class Theme
{
    /**
     * @param array<string, mixed> $tokens
     * @param array<string, mixed> $styles
     */
    public function __construct(
        public string $name = 'default',
        public array $tokens = [],
        public array $styles = [],
    ) {
    }
}
