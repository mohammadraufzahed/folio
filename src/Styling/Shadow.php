<?php

declare(strict_types=1);

namespace Folio\Pdf\Styling;

use Folio\Pdf\Support\Immutable;

final class Shadow
{
    use Immutable;

    private readonly float $offsetX;
    private readonly float $offsetY;
    private readonly float $blurRadius;
    private readonly float $spreadRadius;
    private readonly Color $color;

    private function __construct(
        float $offsetX,
        float $offsetY,
        float $blurRadius,
        float $spreadRadius,
        Color $color
    ) {
        $this->offsetX = $offsetX;
        $this->offsetY = $offsetY;
        $this->blurRadius = max(0.0, $blurRadius);
        $this->spreadRadius = $spreadRadius;
        $this->color = $color;
    }

    public static function make(
        float $offsetX,
        float $offsetY,
        float $blurRadius,
        Color $color,
        float $spreadRadius = 0.0
    ): self {
        return new self($offsetX, $offsetY, $blurRadius, $spreadRadius, $color);
    }

    public function offsetX(): float
    {
        return $this->offsetX;
    }

    public function offsetY(): float
    {
        return $this->offsetY;
    }

    public function blurRadius(): float
    {
        return $this->blurRadius;
    }

    public function spreadRadius(): float
    {
        return $this->spreadRadius;
    }

    public function color(): Color
    {
        return $this->color;
    }
}
