<?php

declare(strict_types=1);

namespace Folio\Pdf\Layout;

use Folio\Pdf\StyleEngine\ComputedStyle;
use Folio\Pdf\Support\Immutable;

final class LayoutBox
{
    use Immutable;

    private readonly Point $position;
    private readonly Size $size;
    private readonly array $children;
    private readonly ?ComputedStyle $computedStyle;

    public function __construct(Point $position, Size $size, array $children = [], ?ComputedStyle $computedStyle = null)
    {
        $this->position = $position;
        $this->size = $size;
        $this->children = array_values($children);
        $this->computedStyle = $computedStyle;
    }

    public static function make(Point $position, Size $size, array $children = [], ?ComputedStyle $computedStyle = null): self
    {
        return new self($position, $size, $children, $computedStyle);
    }

    public static function fromSize(Size $size): self
    {
        return new self(Point::origin(), $size);
    }

    public function position(): Point
    {
        return $this->position;
    }

    public function size(): Size
    {
        return $this->size;
    }

    public function width(): float
    {
        return $this->size->width();
    }

    public function height(): float
    {
        return $this->size->height();
    }

    public function x(): float
    {
        return $this->position->x();
    }

    public function y(): float
    {
        return $this->position->y();
    }

    public function computedStyle(): ?ComputedStyle
    {
        return $this->computedStyle;
    }

    /**
     * @return array<int, LayoutBox>
     */
    public function children(): array
    {
        return $this->children;
    }

    public function withPosition(Point $position): self
    {
        return new self($position, $this->size, $this->children, $this->computedStyle);
    }

    public function withSize(Size $size): self
    {
        return new self($this->position, $size, $this->children, $this->computedStyle);
    }

    public function withChildren(array $children): self
    {
        return new self($this->position, $this->size, $children, $this->computedStyle);
    }

    public function withComputedStyle(?ComputedStyle $computedStyle): self
    {
        return new self($this->position, $this->size, $this->children, $computedStyle);
    }

    public function addChild(LayoutBox $child): self
    {
        return new self($this->position, $this->size, [...$this->children, $child], $this->computedStyle);
    }
}
