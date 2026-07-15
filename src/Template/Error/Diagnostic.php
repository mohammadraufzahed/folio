<?php

declare(strict_types=1);

namespace Folio\Pdf\Template\Error;

final readonly class Diagnostic
{
    public function __construct(
        public Severity $severity,
        public string $message,
        public int $line = 1,
        public int $column = 1,
        public int $length = 1,
        public ?string $file = null,
    ) {
    }

    public function toString(): string
    {
        $loc = '';
        if ($this->file !== null) {
            $loc .= $this->file . ':';
        }
        $loc .= $this->line . ':' . $this->column;
        return sprintf('[%s] %s — %s', $this->severity->name, $loc, $this->message);
    }
}
