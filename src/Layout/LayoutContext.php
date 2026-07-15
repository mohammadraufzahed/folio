<?php

declare(strict_types=1);

namespace Folio\Pdf\Layout;

final class LayoutContext
{
    private readonly float $availableWidth;
    private readonly float $availableHeight;

    public function __construct(float $availableWidth, float $availableHeight)
    {
        $this->availableWidth = max(0.0, $availableWidth);
        $this->availableHeight = max(0.0, $availableHeight);
    }

    public static function make(float $width, float $height): self
    {
        return new self($width, $height);
    }

    public function availableWidth(): float
    {
        return $this->availableWidth;
    }

    public function availableHeight(): float
    {
        return $this->availableHeight;
    }

    public function withWidth(float $width): self
    {
        return new self($width, $this->availableHeight);
    }

    public function withHeight(float $height): self
    {
        return new self($this->availableWidth, $height);
    }
}
