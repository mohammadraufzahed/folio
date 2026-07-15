<?php

declare(strict_types=1);

namespace Folio\Pdf\Contracts;

use Folio\Pdf\Styling\Style;

interface Node
{
    public function style(): ?Style;

    /**
     * @return array<int, Node>
     */
    public function children(): array;

    public function hasChildren(): bool;

    public function type(): string;
}
