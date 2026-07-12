<?php

declare(strict_types=1);

namespace Folio\Pdf\Nodes;

use Folio\Pdf\Contracts\Node;
use Folio\Pdf\Styling\Style;
use Folio\Pdf\Support\Immutable;

/**
 * Base class for all immutable document nodes.
 */
abstract class AbstractNode implements Node
{
    use Immutable;

    protected readonly array $children;

    public function __construct(
        protected readonly ?Style $style = null,
        array $children = []
    ) {
        $this->children = array_values($children);
    }

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
     * Create a new instance with the given children.
     *
     * @param array<int, Node> $children
     */
    protected function withChildren(array $children): static
    {
        return new static($this->style, $children);
    }

    /**
     * Create a new instance with the given style.
     */
    public function withStyle(?Style $style): static
    {
        return new static($style, $this->children);
    }
}
