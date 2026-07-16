<?php

declare(strict_types=1);

namespace Folio\Pdf\Layout;

final readonly class TextWrapResult
{
    /**
     * @param array<int, string> $lines
     */
    public function __construct(
        public array $lines,
        public float $width,
        public float $height,
    ) {
    }
}
