<?php

declare(strict_types=1);

namespace Folio\Pdf\Tests\Layout;

use Folio\Pdf\Document\Document;
use Folio\Pdf\Layout\LayoutContext;
use Folio\Pdf\Layout\LayoutEngine;
use Folio\Pdf\Nodes\Column;
use Folio\Pdf\Nodes\Page;
use Folio\Pdf\Nodes\Text;
use Folio\Pdf\Nodes\TextRun;
use PHPUnit\Framework\TestCase;

final class LayoutEngineTextTest extends TestCase
{
    public function testTextNodeUsesVariableWidthMetrics(): void
    {
        $engine = new LayoutEngine();

        $wide = Text::make('WWWW');
        $narrow = Text::make('iiii');

        $wideBox = $engine->layoutNode($wide, LayoutContext::make(500.0, 500.0));
        $narrowBox = $engine->layoutNode($narrow, LayoutContext::make(500.0, 500.0));

        self::assertGreaterThan($narrowBox->width(), $wideBox->width());
    }

    public function testTextRunIsLaidOut(): void
    {
        $engine = new LayoutEngine();
        $textRun = TextRun::fromText('This is a paragraph that may wrap inside a narrow column.');

        $box = $engine->layoutNode($textRun, LayoutContext::make(120.0, 500.0));

        self::assertGreaterThan(0.0, $box->width());
        self::assertGreaterThan(0.0, $box->height());
    }

    public function testDocumentLayoutUsesRealMetrics(): void
    {
        $page = Page::a4()->withContent(
            Column::make(null, [
                Text::make(str_repeat('A ', 200)),
            ])
        );
        $document = Document::make()->addPage($page);

        $engine = new LayoutEngine();
        $result = $engine->layout($document);

        self::assertSame(1, $result->pageCount());
        self::assertGreaterThan(0.0, $result->layoutBoxes()[0]->height());
    }
}
