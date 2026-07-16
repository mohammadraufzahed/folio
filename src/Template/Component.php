<?php

declare(strict_types=1);

namespace Folio\Pdf\Template;

use Folio\Pdf\Contracts\Node;
use Folio\Pdf\Styling\Style;

final class Component implements Node
{
    /**
     * @param array<string, mixed> $props
     * @param array<string, Node> $slots
     */
    public function __construct(
        private readonly string $name,
        private readonly array $props = [],
        private readonly array $slots = [],
        private readonly ?Style $style = null,
    ) {
    }

    public function name(): string
    {
        return $this->name;
    }

    /**
     * @return array<string, mixed>
     */
    public function props(): array
    {
        return $this->props;
    }

    /**
     * @return array<string, Node>
     */
    public function slots(): array
    {
        return $this->slots;
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
        return 'component';
    }
}
