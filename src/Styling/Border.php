<?php

declare(strict_types=1);

namespace Folio\Pdf\Styling;

use Folio\Pdf\Support\Immutable;

final class Border
{
    use Immutable;

    private readonly float $width;
    private readonly Color $color;
    private readonly BorderStyle $style;

    private function __construct(float $width, Color $color, BorderStyle $style)
    {
        $this->width = max(0.0, $width);
        $this->color = $color;
        $this->style = $style;
    }

    public static function make(float $width, Color $color, BorderStyle $style = BorderStyle::Solid): self
    {
        return new self($width, $color, $style);
    }

    public function width(): float
    {
        return $this->width;
    }

    public function color(): Color
    {
        return $this->color;
    }

    public function style(): BorderStyle
    {
        return $this->style;
    }
}

enum BorderStyle: string
{
    case Solid = 'solid';
    case Dashed = 'dashed';
    case Dotted = 'dotted';
    case Double = 'double';
    case None = 'none';
}
