<?php

declare(strict_types=1);

namespace Folio\Pdf\Tests\Pagination;

use Folio\Pdf\Layout\LayoutContext;
use Folio\Pdf\Layout\LayoutEngine;
use Folio\Pdf\Layout\Size;
use Folio\Pdf\Nodes\Column;
use Folio\Pdf\Nodes\Heading;
use Folio\Pdf\Nodes\Page;
use Folio\Pdf\Nodes\Table;
use Folio\Pdf\Nodes\TableCell;
use Folio\Pdf\Nodes\TableRow;
use Folio\Pdf\Nodes\Text;
use Folio\Pdf\Pagination\Paginator;
use PHPUnit\Framework\TestCase;

final class PaginatorTest extends TestCase
{
    public function testSplitSingleColumnAcrossPages(): void
    {
        $engine = new LayoutEngine();
        $page = Page::make(200.0, 100.0)
            ->withContent(
                Column::make(null, [
                    Text::make(str_repeat('word ', 200)),
                    Text::make('Footer text'),
                ])
            );

        $layout = $engine->layoutNode($page, LayoutContext::make(200.0, 100.0));

        $paginator = new Paginator($engine);
        $paged = $paginator->paginate($layout->children()[0], Size::make(200.0, 50.0));

        self::assertGreaterThan(1, $paged->pageCount());
    }

    public function testTableRowsSplitAcrossPages(): void
    {
        $engine = new LayoutEngine();
        $table = Table::make([
            TableRow::make([
                TableCell::make(Text::make('Header')),
            ]),
            TableRow::make([
                TableCell::make(Text::make('Row 1 with some content that may wrap')),
            ]),
            TableRow::make([
                TableCell::make(Text::make('Row 2 with some content that may wrap')),
            ]),
            TableRow::make([
                TableCell::make(Text::make('Row 3 with some content that may wrap')),
            ]),
        ]);

        $layout = $engine->layoutNode($table, LayoutContext::make(200.0, 400.0));

        $paginator = new Paginator($engine);
        $paged = $paginator->paginate($layout, Size::make(200.0, 40.0));

        self::assertGreaterThan(1, $paged->pageCount());
    }

    public function testHeadingSplitsAcrossPages(): void
    {
        $engine = new LayoutEngine();
        $heading = Heading::h1(str_repeat('Big title ', 80));

        $layout = $engine->layoutNode($heading, LayoutContext::make(300.0, 1000.0));

        $paginator = new Paginator($engine);
        $paged = $paginator->paginate($layout, Size::make(300.0, 60.0));

        self::assertGreaterThanOrEqual(1, $paged->pageCount());
    }
}
