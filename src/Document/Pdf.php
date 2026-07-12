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

    /** @var array{title?: string, subtitle?: string, badge?: string, rightTitle?: string, rightSubtitle?: string}|null */
    private ?array $pageHeader = null;

    /** @var array{left?: string, center?: string, right?: string, showPageNumber?: bool}|null */
    private ?array $pageFooter = null;

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
     * Set repeating page header chrome (drawn on every page).
     *
     * @param array{title?: string, subtitle?: string, badge?: string, rightTitle?: string, rightSubtitle?: string} $header
     */
    public function pageHeader(array $header): self
    {
        $newInstance = clone $this;
        $newInstance->pageHeader = $header;
        if ($newInstance->document !== null) {
            $newInstance->document = $newInstance->document->withPageHeader($header);
        }
        return $newInstance;
    }

    /**
     * Set repeating page footer chrome (drawn on every page).
     *
     * @param array{left?: string, center?: string, right?: string, showPageNumber?: bool} $footer
     */
    public function pageFooter(array $footer): self
    {
        $newInstance = clone $this;
        $newInstance->pageFooter = $footer;
        if ($newInstance->document !== null) {
            $newInstance->document = $newInstance->document->withPageFooter($footer);
        }
        return $newInstance;
    }

    /**
     * Add a page to the document.
     */
    public function page(Page $page): self
    {
        $newInstance = clone $this;
        $document = $newInstance->document ?? Document::make();
        if ($newInstance->pageHeader !== null) {
            $document = $document->withPageHeader($newInstance->pageHeader);
        }
        if ($newInstance->pageFooter !== null) {
            $document = $document->withPageFooter($newInstance->pageFooter);
        }
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
        if ($newInstance->pageHeader !== null) {
            $document = $document->withPageHeader($newInstance->pageHeader);
        }
        if ($newInstance->pageFooter !== null) {
            $document = $document->withPageFooter($newInstance->pageFooter);
        }
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
        if ($this->pageHeader !== null) {
            $document = $document->withPageHeader($this->pageHeader);
        }
        if ($this->pageFooter !== null) {
            $document = $document->withPageFooter($this->pageFooter);
        }
        return $document->generate();
    }
}
