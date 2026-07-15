<?php

declare(strict_types=1);

namespace Folio\Pdf\Layout;

use Folio\Pdf\Support\Immutable;

final class Size
{
    use Immutable;

    private readonly float $width;
    private readonly float $height;

    public function __construct(float $width, float $height)
    {
        $this->width = max(0.0, $width);
        $this->height = max(0.0, $height);
    }

    public static function make(float $width, float $height): self
    {
        return new self($width, $height);
    }

    public static function zero(): self
    {
        return new self(0.0, 0.0);
    }

    public function width(): float
    {
        return $this->width;
    }

    public function height(): float
    {
        return $this->height;
    }

    public function withWidth(float $width): self
    {
        return new self($width, $this->height);
    }

    public function withHeight(float $height): self
    {
        return new self($this->width, $height);
    }

    public function add(Size $other): self
    {
        return new self($this->width + $other->width, $this->height + $other->height);
    }

    public function toArray(): array
    {
        return ['width' => $this->width, 'height' => $this->height];
    }
}
