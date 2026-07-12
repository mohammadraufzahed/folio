<?php

declare(strict_types=1);

namespace Folio\Pdf\Nodes;

use Folio\Pdf\Contracts\Node;
use Folio\Pdf\Styling\Style;

/**
 * Container node that arranges children vertically.
 */
final class Column extends AbstractNode
{
    public static function make(?Style $style = null, array $children = []): self
    {
        return new self($style, $children);
    }

    /**
     * Add a child to the column.
     */
    public function addChild(Node $child): self
    {
        return $this->withChildren([...$this->children, $child]);
    }

    /**
     * Add multiple children to the column.
     *
     * @param array<int, Node> $children
     */
    public function addChildren(array $children): self
    {
        return $this->withChildren([...$this->children, ...$children]);
    }
}
