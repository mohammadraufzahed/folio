<?php

declare(strict_types=1);

namespace Folio\Pdf\Contracts;

interface PdfWriter
{
    public function save(string $path): void;

    public function toString(): string;

    /**
     * @return string
     */
    public function toBytes(): string;
}
