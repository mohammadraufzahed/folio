<?php

declare(strict_types=1);

namespace Folio\Pdf\Document;

use Folio\Pdf\Contracts\Node;
use Folio\Pdf\Contracts\PdfWriter;
use Folio\Pdf\Nodes\Page;

/**
 * Fluent builder for creating PDF documents.
 */
final class Pdf
{
    private ?Document $document = null;
    private ?string $theme = null;

    private function __construct()
    {
    }

    public static function make(): self
    {
        return new self();
    }

    /**
     * Set the theme for this document.
     */
    public function theme(string $name): self
    {
        $newInstance = clone $this;
        $newInstance->theme = $name;
        return $newInstance;
    }

    /**
     * Add a page to the document.
     */
    public function page(Page $page): self
    {
        $newInstance = clone $this;
        $document = $newInstance->document ?? Document::make();
        $newInstance->document = $document->addPage($page);
        return $newInstance;
    }

    /**
     * Set the document content.
     */
    public function content(Node $node): self
    {
        $newInstance = clone $this;
        $document = $newInstance->document ?? Document::make();
        // Create a page with the content
        $page = Page::make()->withContent($node);
        $newInstance->document = $document->addPage($page);
        return $newInstance;
    }

    /**
     * Generate and save the PDF to a file.
     */
    public function save(string $path): void
    {
        $this->generate()->save($path);
    }

    /**
     * Generate and return the PDF as a string.
     */
    public function toString(): string
    {
        return $this->generate()->toString();
    }

    /**
     * Generate and return the PDF as bytes.
     */
    public function toBytes(): string
    {
        return $this->generate()->toBytes();
    }

    /**
     * Generate the PDF.
     */
    private function generate(): PdfWriter
    {
        $document = $this->document ?? Document::make();
        return $document->generate();
    }
}
