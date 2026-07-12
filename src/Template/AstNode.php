<?php

declare(strict_types=1);

namespace Folio\Pdf\Template;

/**
 * AST node for the template language.
 */
final readonly class AstNode
{
    /**
     * @param array<int, AstNode> $children
     * @param array<string, mixed> $attributes
     */
    public function __construct(
        public string $type,
        public array $children = [],
        public array $attributes = []
    ) {}

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
