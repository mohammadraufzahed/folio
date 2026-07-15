<?php

declare(strict_types=1);

namespace Folio\Pdf\Contracts;

interface HasChildren extends Node
{
    /**
     * @param array<int, Node> $children
     */
    public function withChildren(array $children): Node;
}
