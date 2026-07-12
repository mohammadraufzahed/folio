<?php

declare(strict_types=1);

namespace Folio\Pdf\Contracts;

use Folio\Pdf\Styling\Style;

/**
 * Interface for all document nodes in the AST.
 */
interface Node
{
    /**
     * Get the style for this node.
     */
    public function style(): ?Style;

    /**
     * Get the children of this node.
     *
     * @return array<int, Node>
     */
    public function children(): array;

    /**
     * Check if this node has children.
     */
    public function hasChildren(): bool;

    /**
     * Get the node type identifier.
     */
    public function type(): string;
}
