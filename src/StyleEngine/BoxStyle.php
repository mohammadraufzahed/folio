<?php

declare(strict_types=1);

namespace Folio\Pdf\StyleEngine;

use Folio\Pdf\Styling\Border;
use Folio\Pdf\Styling\Color;
use Folio\Pdf\Styling\Length;
use Folio\Pdf\Styling\Shadow;

final readonly class BoxStyle
{
    public function __construct(
        public ?float $padding = null,
        public ?float $paddingTop = null,
        public ?float $paddingRight = null,
        public ?float $paddingBottom = null,
        public ?float $paddingLeft = null,
        public ?float $margin = null,
        public ?float $marginTop = null,
        public ?float $marginRight = null,
        public ?float $marginBottom = null,
        public ?float $marginLeft = null,
        public ?Border $border = null,
        public ?float $borderWidth = null,
        public ?Color $borderColor = null,
        public ?float $radius = null,
        public ?Color $background = null,
        public ?Shadow $shadow = null,
        public ?Length $width = null,
        public ?Length $height = null,
    ) {
    }
}
