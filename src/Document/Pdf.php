<?php

declare(strict_types=1);

namespace Folio\Pdf\Document;

use Folio\Pdf\Layout\LayoutEngine;
use Folio\Pdf\Nodes\Page;
use Folio\Pdf\Renderer\Pdf1_7Renderer;

final class Pdf
{
    private ?Document $document = null;

    private function __construct()
    {
    }

    public static function make(): self
    {
        return new self();
    }

    public function theme(string $name): self
    {
        return clone $this;
    }

    /**
     * @param array<string, mixed> $header
     */
    public function pageHeader(array $header): self
    {
        return $this;
    }

    /**
     * @param array<string, mixed> $footer
     */
    public function pageFooter(array $footer): self
    {
        return $this;
    }

    public function page(Page $page): self
    {
        $newInstance = clone $this;
        $document = $newInstance->document ?? Document::make();
        $newInstance->document = $document->addPage($page);

        return $newInstance;
    }

    public function content(\Folio\Pdf\Contracts\Node $node): self
    {
        return $this->page(Page::make()->withContent($node));
    }

    public function save(string $path): void
    {
        file_put_contents($path, $this->toBytes());
    }

    public function toString(): string
    {
        return $this->toBytes();
    }

    public function toBytes(): string
    {
        return $this->render();
    }

    public function document(): Document
    {
        return $this->document ?? Document::make();
    }

    private function render(): string
    {
        $document = $this->document();
        $layout = (new LayoutEngine())->layout($document);

        return (new Pdf1_7Renderer())->render($document, $layout);
    }
}
