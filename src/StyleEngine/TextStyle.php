<?php

declare(strict_types=1);

namespace Folio\Pdf\StyleEngine;

use Folio\Pdf\Styling\Alignment;
use Folio\Pdf\Styling\Color;
use Folio\Pdf\Styling\FontWeight;

final readonly class TextStyle
{
    public function __construct(
        public ?string $font = null,
        public ?float $fontSize = null,
        public ?FontWeight $fontWeight = null,
        public ?Color $color = null,
        public ?float $lineHeight = null,
        public ?float $letterSpacing = null,
        public ?Alignment $alignment = null,
        public ?string $textDecoration = null,
        public ?string $textTransform = null,
        public ?string $fontFeatureSettings = null,
    ) {
    }
}
