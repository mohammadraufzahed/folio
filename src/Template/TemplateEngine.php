<?php

declare(strict_types=1);

namespace Folio\Pdf\Template;

use Folio\Pdf\Contracts\TemplateCompiler;
use Folio\Pdf\Document\Document;
use Folio\Pdf\Layout\LayoutEngine;
use Folio\Pdf\Ports\RendererPort;
use Folio\Pdf\Renderer\Pdf1_7Renderer;

final class TemplateEngine
{
    private TemplateCompiler $compiler;
    private LayoutEngine $layoutEngine;
    private RendererPort $renderer;
    private ?Folio2Preprocessor $preprocessor = null;

    public function __construct(
        ?TemplateCompiler $compiler = null,
        ?LayoutEngine $layoutEngine = null,
        ?RendererPort $renderer = null,
    ) {
        $this->compiler = $compiler ?? new PhpTemplateCompiler();
        $this->layoutEngine = $layoutEngine ?? new LayoutEngine();
        $this->renderer = $renderer ?? new Pdf1_7Renderer();
    }

    /**
     * @param array<string, mixed> $data
     */
    public function compileToDocument(string $template, array $data = [], ?string $path = null): Document
    {
        $template = $this->preprocess($template, $path);

        if ($this->compiler instanceof PhpTemplateCompiler) {
            if ($path !== null) {
                $this->compiler->setBaseDir(dirname($path));
            }

            $pdf = $this->compiler->render($template, $data);

            return $pdf->document();
        }

        throw new \RuntimeException('Only PhpTemplateCompiler is currently supported by TemplateEngine.');
    }

    /**
     * @param array<string, mixed> $data
     */
    public function render(string $template, array $data = []): string
    {
        $document = $this->compileToDocument($template, $data);

        return $this->renderDocument($document);
    }

    /**
     * @param array<string, mixed> $data
     */
    public function renderFile(string $path, array $data = []): string
    {
        $content = file_get_contents($path);

        if ($content === false) {
            throw new \RuntimeException("Unable to read template: {$path}");
        }

        $document = $this->compileToDocument($content, $data, $path);

        return $this->renderDocument($document);
    }

    public function renderDocument(Document $document): string
    {
        $layout = $this->layoutEngine->layout($document);

        return $this->renderer->render($document, $layout);
    }

    public function enableFolio2Syntax(?string $baseDir = null): self
    {
        $this->preprocessor = new Folio2Preprocessor($baseDir);

        return $this;
    }

    public function setCompiler(TemplateCompiler $compiler): self
    {
        $this->compiler = $compiler;

        return $this;
    }

    public function setLayoutEngine(LayoutEngine $layoutEngine): self
    {
        $this->layoutEngine = $layoutEngine;

        return $this;
    }

    public function setRenderer(RendererPort $renderer): self
    {
        $this->renderer = $renderer;

        return $this;
    }

    private function preprocess(string $template, ?string $path): string
    {
        if ($this->preprocessor === null) {
            return $template;
        }

        $baseDir = $path !== null ? dirname($path) : null;

        if ($baseDir !== null) {
            $this->preprocessor = new Folio2Preprocessor($baseDir);
        }

        return $this->preprocessor->process($template, $path);
    }
}
