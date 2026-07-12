<?php

declare(strict_types=1);

namespace Folio\Pdf\Template;

/**
 * Token produced by the lexer.
 *
 * Carries byte offset plus 1-based line and column for diagnostics.
 */
final readonly class Token
{
    public function __construct(
        public TokenType $type,
        public string $value,
        public int $position,
        public int $line = 1,
        public int $column = 1,
        public int $length = 1,
    ) {
    }

    public function __toString(): string
    {
        return sprintf(
            'Token(%s, "%s", %d:%d)',
            $this->type->name,
            $this->value,
            $this->line,
            $this->column,
        );
    }
}
