<?php

declare(strict_types=1);

namespace Folio\Pdf\Document;

use Folio\Pdf\Contracts\Node;
use Folio\Pdf\Contracts\PdfWriter;
use Folio\Pdf\Nodes\Page;

final class Pdf
{
    private ?Document $document = null;

    private ?string $theme = null;

    private ?array $pageHeader = null;

    private ?array $pageFooter = null;

    private function __construct()
    {
    }

    public static function make(): self
    {
        return new self();
    }

    public function theme(string $name): self
    {
        $newInstance = clone $this;
        $newInstance->theme = $name;
        $newInstance->pageHeader = $this->chromeWithTheme($newInstance->pageHeader, $name);
        $newInstance->pageFooter = $this->chromeWithTheme($newInstance->pageFooter, $name);

        if ($newInstance->document !== null) {
            $document = $newInstance->document;
            if ($newInstance->pageHeader !== null) {
                $document = $document->withPageHeader($newInstance->pageHeader);
            }
            if ($newInstance->pageFooter !== null) {
                $document = $document->withPageFooter($newInstance->pageFooter);
            }
            $newInstance->document = $document;
        }

        return $newInstance;
    }

    /**
     * @param array{title?: string, subtitle?: string, badge?: string, rightTitle?: string, rightSubtitle?: string} $header
     */
    public function pageHeader(array $header): self
    {
        $newInstance = clone $this;
        $newInstance->pageHeader = $this->chromeWithTheme($header, $this->theme);
        if ($newInstance->document !== null) {
            $newInstance->document = $newInstance->document->withPageHeader($newInstance->pageHeader);
        }

        return $newInstance;
    }

    /**
     * @param array{left?: string, center?: string, right?: string, showPageNumber?: bool} $footer
     */
    public function pageFooter(array $footer): self
    {
        $newInstance = clone $this;
        $newInstance->pageFooter = $this->chromeWithTheme($footer, $this->theme);
        if ($newInstance->document !== null) {
            $newInstance->document = $newInstance->document->withPageFooter($newInstance->pageFooter);
        }

        return $newInstance;
    }

    public function page(Page $page): self
    {
        $newInstance = clone $this;
        $document = $newInstance->document ?? Document::make();
        $document = $this->applyChrome($document, $newInstance);
        $newInstance->document = $document->addPage($page);

        return $newInstance;
    }

    public function content(Node $node): self
    {
        $newInstance = clone $this;
        $document = $newInstance->document ?? Document::make();
        $document = $this->applyChrome($document, $newInstance);
        $page = Page::make()->withContent($node);
        $newInstance->document = $document->addPage($page);

        return $newInstance;
    }

    public function save(string $path): void
    {
        $this->generate()->save($path);
    }

    public function toString(): string
    {
        return $this->generate()->toString();
    }

    public function toBytes(): string
    {
        return $this->generate()->toBytes();
    }

    private function generate(): PdfWriter
    {
        $document = $this->document ?? Document::make();
        $document = $this->applyChrome($document, $this);

        return $document->generate();
    }

    private function applyChrome(Document $document, self $instance): Document
    {
        if ($instance->pageHeader !== null) {
            $document = $document->withPageHeader($instance->pageHeader);
        }
        if ($instance->pageFooter !== null) {
            $document = $document->withPageFooter($instance->pageFooter);
        }

        return $document;
    }

    /**
     * @param array<string, mixed>|null $chrome
     *
     * @return array<string, mixed>|null
     */
    private function chromeWithTheme(?array $chrome, ?string $theme): ?array
    {
        if ($chrome === null) {
            return null;
        }

        if ($theme !== null && !isset($chrome['theme'])) {
            $chrome['theme'] = $theme;
        }

        return $chrome;
    }
}
