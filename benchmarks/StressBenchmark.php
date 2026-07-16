<?php

declare(strict_types=1);

namespace Folio\Pdf\Benchmarks;

use Folio\Pdf\Document\Document;
use Folio\Pdf\Layout\LayoutEngine;
use Folio\Pdf\Nodes\Column;
use Folio\Pdf\Nodes\Page;
use Folio\Pdf\Nodes\Table;
use Folio\Pdf\Nodes\TableCell;
use Folio\Pdf\Nodes\TableRow;
use Folio\Pdf\Nodes\Text;
use Folio\Pdf\Renderer\Pdf1_7Renderer;

final class StressBenchmark extends Benchmark
{
    public function __construct()
    {
        parent::__construct('stress');
    }

    protected function warmup(): void
    {
        $this->execute();
    }

    protected function execute(): void
    {
        $rows = [];

        for ($i = 0; $i < 1000; $i++) {
            $rows[] = TableRow::make([
                TableCell::make(Text::make((string) $i)),
                TableCell::make(Text::make('Item ' . $i)),
                TableCell::make(Text::make((string) ($i * 1.5))),
            ]);
        }

        $document = Document::make()->addPage(
            Page::make(595.0, 842.0)
                ->withContent(Column::make(null, [Table::make($rows)])),
        );

        $layout = (new LayoutEngine())->layout($document);
        (new Pdf1_7Renderer())->render($document, $layout);
    }
}
