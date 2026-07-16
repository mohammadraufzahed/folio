<?php

declare(strict_types=1);

namespace Folio\Pdf\Ports;

final readonly class TextMetrics
{
    public function __construct(
        public float $width,
        public float $height,
        public float $baseline,
        public float $advance,
    ) {
    }
}
