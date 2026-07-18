<?php

declare(strict_types=1);

namespace Folio\Pdf\Document;

use Folio\Pdf\Infrastructure\Theme\FolioThemeRepository;
use Folio\Pdf\Layout\LayoutEngine;
use Folio\Pdf\Nodes\Page;
use Folio\Pdf\Ports\ThemeRepositoryPort;
use Folio\Pdf\Renderer\Pdf1_7Renderer;
use Folio\Pdf\StyleEngine\StyleSheet;
use Folio\Pdf\StyleEngine\Theme;

final class Pdf
{
    private ?Document $document = null;

    private ?Theme $theme = null;

    private ?StyleSheet $styleSheet = null;

    private static ?ThemeRepositoryPort $defaultThemeRepository = null;

    private function __construct()
    {
    }

    public static function make(): self
    {
        return new self();
    }

    public static function setDefaultThemeRepository(ThemeRepositoryPort $repository): void
    {
        self::$defaultThemeRepository = $repository;
    }

    public static function defaultThemeRepository(): ThemeRepositoryPort
    {
        return self::$defaultThemeRepository ?? new FolioThemeRepository();
    }

    public function theme(string $name, ?ThemeRepositoryPort $repository = null): self
    {
        $newInstance = clone $this;
        $newInstance->theme = ($repository ?? self::defaultThemeRepository())->load($name);

        return $newInstance;
    }

    public function withStyleSheet(StyleSheet $styleSheet): self
    {
        $newInstance = clone $this;
        $newInstance->styleSheet = $styleSheet;

        return $newInstance;
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
        $document = ($newInstance->document ?? Document::make())
            ->withTheme($newInstance->theme)
            ->withStyleSheet($newInstance->styleSheet)
            ->addPage($page);
        $newInstance->document = $document;

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
        $layout = (new LayoutEngine(theme: $this->theme, styleSheet: $this->styleSheet))->layout($document);

        return (new Pdf1_7Renderer())->render($document, $layout);
    }
}
