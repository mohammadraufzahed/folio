<?php

declare(strict_types=1);

namespace Folio\Pdf\StyleEngine;

use Folio\Pdf\Styling\Color;

final readonly class PaintStyle
{
    public function __construct(
        public ?Color $fill = null,
        public ?Color $stroke = null,
        public ?float $strokeWidth = null,
        public ?float $opacity = null,
        public ?string $blendMode = null,
    ) {
    }
}
