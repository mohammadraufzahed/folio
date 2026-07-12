<?php

declare(strict_types=1);

namespace Folio\Pdf\Template\Error;

use Exception;

/**
 * Thrown when the template cannot be parsed or compiled.
 *
 * Carries a source span (line, column, length) so LSP and CLI can
 * point at the exact location of the problem.
 */
class TemplateError extends Exception
{
    public function __construct(
        string $message,
        public readonly int $sourceLine = 1,
        public readonly int $sourceColumn = 1,
        public readonly int $spanLength = 1,
        public readonly ?string $sourceFile = null,
        ?\Throwable $previous = null,
    ) {
        $location = '';
        if ($this->sourceFile !== null) {
            $location .= $this->sourceFile . ':';
        }
        $location .= $this->sourceLine . ':' . $this->sourceColumn;
        parent::__construct("{$location} — {$message}", 0, $previous);
    }
}
