<?php

declare(strict_types=1);

namespace Folio\Pdf\Contracts;

/**
 * Interface for PDF writers.
 */
interface PdfWriter
{
    /**
     * Write the PDF content to a file.
     */
    public function save(string $path): void;

    /**
     * Get the PDF content as a string.
     */
    public function toString(): string;

    /**
     * Get the PDF content as bytes.
     *
     * @return string
     */
    public function toBytes(): string;
}
