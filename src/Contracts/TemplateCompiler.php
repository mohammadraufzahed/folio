<?php

declare(strict_types=1);

namespace Folio\Pdf\Contracts;

interface TemplateCompiler
{
    public function compile(string $template): string;

    public function compileFile(string $path): string;

    public function getCachePath(string $template): string;
}
