<?php

declare(strict_types=1);

namespace Folio\Pdf\StyleEngine;

final readonly class ComputedStyle
{
    public function __construct(
        public BoxStyle $box = new BoxStyle(),
        public TextStyle $text = new TextStyle(),
        public LayoutStyle $layout = new LayoutStyle(),
        public PaintStyle $paint = new PaintStyle(),
    ) {
    }
}
