<?php

declare(strict_types=1);

namespace Folio\Pdf\Renderer;

final readonly class RenderContext
{
    public function __construct(
        public float $pageWidth,
        public float $pageHeight,
        public float $x = 0.0,
        public float $y = 0.0,
    ) {
    }

    public static function make(
        float $pageWidth,
        float $pageHeight,
        float $x = 0.0,
        float $y = 0.0,
    ): self {
        return new self($pageWidth, $pageHeight, $x, $y);
    }
}
