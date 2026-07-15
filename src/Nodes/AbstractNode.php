<?php

declare(strict_types=1);

namespace Folio\Pdf\Nodes;

use Folio\Pdf\Contracts\HasChildren;
use Folio\Pdf\Contracts\Node;
use Folio\Pdf\Styling\Style;
use Folio\Pdf\Support\Immutable;

abstract class AbstractNode implements HasChildren
{
    use Immutable;

    protected readonly array $children;

    public function __construct(
        protected readonly ?Style $style = null,
        array $children = []
    ) {
        $this->children = array_values($children);
    }

    abstract protected function copy(?Style $style, array $children): static;

    public function style(): ?Style
    {
        return $this->style;
    }

    public function children(): array
    {
        return $this->children;
    }

    public function hasChildren(): bool
    {
        return !empty($this->children);
    }

    public function type(): string
    {
        return static::class;
    }

    /**
     * @param array<int, Node> $children
     */
    public function withChildren(array $children): static
    {
        return $this->copy($this->style, $children);
    }

    public function withStyle(?Style $style): static
    {
        return $this->copy($style, $this->children);
    }
}
