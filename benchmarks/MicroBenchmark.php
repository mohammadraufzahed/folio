<?php

declare(strict_types=1);

namespace Folio\Pdf\Benchmarks;

use Folio\Pdf\Document\Document;
use Folio\Pdf\Layout\LayoutEngine;
use Folio\Pdf\Nodes\Page;
use Folio\Pdf\Nodes\Text;
use Folio\Pdf\Renderer\Pdf1_7Renderer;

final class MicroBenchmark extends Benchmark
{
    protected function warmup(): void
    {
        $this->execute();
    }

    protected function execute(): void
    {
        $document = Document::make()->addPage(
            Page::make(595.0, 842.0)
                ->withContent(Text::make('Hello World')),
        );

        $layout = (new LayoutEngine())->layout($document);
        (new Pdf1_7Renderer())->render($document, $layout);
    }
}
