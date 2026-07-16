<?php

declare(strict_types=1);

namespace Folio\Pdf\Benchmarks;

use Folio\Pdf\Document\Document;
use Folio\Pdf\Layout\LayoutEngine;
use Folio\Pdf\Nodes\Column;
use Folio\Pdf\Nodes\Heading;
use Folio\Pdf\Nodes\Page;
use Folio\Pdf\Nodes\Text;
use Folio\Pdf\Renderer\Pdf1_7Renderer;

final class DocumentBenchmark extends Benchmark
{
    protected function warmup(): void
    {
        $this->execute();
    }

    protected function execute(): void
    {
        $children = [];

        for ($i = 0; $i < 50; $i++) {
            $children[] = Heading::h2('Section ' . $i);
            $children[] = Text::make(str_repeat('Lorem ipsum dolor sit amet. ', 10));
        }

        $document = Document::make()->addPage(
            Page::make(595.0, 842.0)
                ->withContent(Column::make(null, $children)),
        );

        $layout = (new LayoutEngine())->layout($document);
        (new Pdf1_7Renderer())->render($document, $layout);
    }
}
