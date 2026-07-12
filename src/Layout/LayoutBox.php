<?php

declare(strict_types=1);

namespace Folio\Pdf\Layout;

use Folio\Pdf\Support\Immutable;

/**
 * Represents a layout box with position and size.
 */
final class LayoutBox
{
    use Immutable;

    private readonly Point $position;
    private readonly Size $size;
    private readonly array $children;

    public function __construct(Point $position, Size $size, array $children = [])
    {
        $this->position = $position;
        $this->size = $size;
        $this->children = array_values($children);
    }

    public static function make(Point $position, Size $size): self
    {
        return new self($position, $size);
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

    /**
     * @return array<int, LayoutBox>
     */
    public function children(): array
    {
        return $this->children;
    }

    public function withPosition(Point $position): self
    {
        return new self($position, $this->size, $this->children);
    }

    public function withSize(Size $size): self
    {
        return new self($this->position, $size, $this->children);
    }

    public function withChildren(array $children): self
    {
        return new self($this->position, $this->size, $children);
    }

    public function addChild(LayoutBox $child): self
    {
        return new self($this->position, $this->size, [...$this->children, $child]);
    }
}
