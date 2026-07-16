<?php

declare(strict_types=1);

namespace Folio\Pdf\Ports;

use Folio\Pdf\Font\Font;

interface FontMetricsPort
{
    public function measure(string $text, Font $font, float $size): TextMetrics;

    public function lineHeight(Font $font, float $size): float;

    public function supportedCharacters(Font $font): UnicodeRangeSet;
}
