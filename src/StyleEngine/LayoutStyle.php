<?php

declare(strict_types=1);

namespace Folio\Pdf\StyleEngine;

use Folio\Pdf\Styling\Length;

final readonly class LayoutStyle
{
    public function __construct(
        public ?string $display = null,
        public ?string $direction = null,
        public ?bool $wrap = null,
        public ?string $justifyContent = null,
        public ?string $alignItems = null,
        public ?string $alignContent = null,
        public ?float $gap = null,
        public ?float $rowGap = null,
        public ?float $columnGap = null,
        public ?int $order = null,
        public ?float $grow = null,
        public ?float $shrink = null,
        public ?Length $basis = null,
        public ?Length $width = null,
        public ?Length $height = null,
        public ?Length $minWidth = null,
        public ?Length $maxWidth = null,
        public ?Length $minHeight = null,
        public ?Length $maxHeight = null,
        public ?string $position = null,
        public ?float $top = null,
        public ?float $right = null,
        public ?float $bottom = null,
        public ?float $left = null,
        public ?int $zIndex = null,
    ) {
    }
}
