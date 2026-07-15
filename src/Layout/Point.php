<?php

declare(strict_types=1);

namespace Folio\Pdf\Layout;

use Folio\Pdf\Support\Immutable;

final class Point
{
    use Immutable;

    private readonly float $x;
    private readonly float $y;

    public function __construct(float $x, float $y)
    {
        $this->x = $x;
        $this->y = $y;
    }

    public static function make(float $x, float $y): self
    {
        return new self($x, $y);
    }

    public static function origin(): self
    {
        return new self(0.0, 0.0);
    }

    public function x(): float
    {
        return $this->x;
    }

    public function y(): float
    {
        return $this->y;
    }

    public function withX(float $x): self
    {
        return new self($x, $this->y);
    }

    public function withY(float $y): self
    {
        return new self($this->x, $y);
    }

    public function add(Point $other): self
    {
        return new self($this->x + $other->x, $this->y + $other->y);
    }

    public function toArray(): array
    {
        return ['x' => $this->x, 'y' => $this->y];
    }
}
