<?php

declare(strict_types=1);

namespace Folio\Pdf\Nodes;

use Folio\Pdf\Contracts\Node;
use Folio\Pdf\Styling\Style;

final class Column extends AbstractNode
{
    public static function make(?Style $style = null, array $children = []): self
    {
        return new self($style, $children);
    }

    public function addChild(Node $child): self
    {
        return $this->withChildren([...$this->children, $child]);
    }

    /**
     * @param array<int, Node> $children
     */
    public function addChildren(array $children): self
    {
        return $this->withChildren([...$this->children, ...$children]);
    }

    protected function copy(?Style $style, array $children): static
    {
        return new self($style, $children);
    }
}
