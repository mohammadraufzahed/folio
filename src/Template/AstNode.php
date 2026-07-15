<?php

declare(strict_types=1);

namespace Folio\Pdf\Template;

final readonly class AstNode
{
    /**
     * @var array<int, AstNode>
     */
    public array $children;

    /**
     * @var array<string, mixed>
     */
    public array $attributes;

    /**
     * @param array<int, AstNode> $children
     * @param array<string, mixed> $attributes
     */
    public function __construct(
        public string $type,
        array $children = [],
        array $attributes = []
    ) {
        $this->children = array_values($children);
        $this->attributes = $attributes;
    }

    public function getChild(int $index): ?AstNode
    {
        return $this->children[$index] ?? null;
    }

    public function getAttribute(string $key): mixed
    {
        return $this->attributes[$key] ?? null;
    }

    public function hasAttribute(string $key): bool
    {
        return isset($this->attributes[$key]);
    }
}
