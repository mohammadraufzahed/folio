<?php

declare(strict_types=1);

namespace Folio\Pdf\Template;

/**
 * Token produced by the lexer.
 */
final readonly class Token
{
    public function __construct(
        public TokenType $type,
        public string $value,
        public int $position
    ) {
    }

    public function __toString(): string
    {
        return sprintf('Token(%s, "%s", %d)', $this->type->name, $this->value, $this->position);
    }
}
