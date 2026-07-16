<?php

declare(strict_types=1);

namespace Folio\Pdf\Template;

use Folio\Pdf\Contracts\Node;
use Folio\Pdf\Styling\Style;

final class Slot implements Node
{
    public function __construct(
        private readonly string $name,
        private readonly ?Style $style = null,
    ) {
    }

    public function name(): string
    {
        return $this->name;
    }

    public function style(): ?Style
    {
        return $this->style;
    }

    /**
     * @return array<int, Node>
     */
    public function children(): array
    {
        return [];
    }

    public function hasChildren(): bool
    {
        return false;
    }

    public function type(): string
    {
        return 'slot';
    }
}
