<?php

declare(strict_types=1);

namespace Folio\Pdf\Contracts;

/**
 * Interface for template compilers.
 */
interface TemplateCompiler
{
    /**
     * Compile a template string to PHP code.
     */
    public function compile(string $template): string;

    /**
     * Compile a template file to PHP code.
     */
    public function compileFile(string $path): string;

    /**
     * Get the cache path for a compiled template.
     */
    public function getCachePath(string $template): string;
}
