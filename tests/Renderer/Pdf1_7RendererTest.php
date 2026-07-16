<?php

declare(strict_types=1);

namespace Folio\Pdf\Tests\Renderer;

use Folio\Pdf\Document\Document;
use Folio\Pdf\Layout\LayoutEngine;
use Folio\Pdf\Nodes\Column;
use Folio\Pdf\Nodes\Heading;
use Folio\Pdf\Nodes\Page;
use Folio\Pdf\Nodes\Text;
use Folio\Pdf\Renderer\Pdf1_7Renderer;
use PHPUnit\Framework\TestCase;

final class Pdf1_7RendererTest extends TestCase
{
    public function testRendersSimpleDocument(): void
    {
        $document = Document::make()->addPage(
            Page::make(200.0, 100.0)
                ->withContent(Text::make('Hello Folio')),
        );

        $layout = (new LayoutEngine())->layout($document);
        $renderer = new Pdf1_7Renderer(null, false);
        $pdf = $renderer->render($document, $layout);

        self::assertStringStartsWith('%PDF-1.7', $pdf);
        self::assertStringContainsString('BT', $pdf);
        self::assertStringContainsString('ET', $pdf);
    }

    public function testRendersMultiPageDocument(): void
    {
        $document = Document::make()->addPage(
            Page::make(200.0, 60.0)
                ->withContent(
                    Column::make(null, [
                        Text::make(str_repeat('word ', 80)),
                    ])
                ),
        );

        $layout = (new LayoutEngine())->layout($document);
        $renderer = new Pdf1_7Renderer(null, false);
        $pdf = $renderer->render($document, $layout);

        self::assertStringStartsWith('%PDF-1.7', $pdf);
    }

    public function testRendersHeading(): void
    {
        $document = Document::make()->addPage(
            Page::make(200.0, 100.0)
                ->withContent(Heading::h1('Title')),
        );

        $layout = (new LayoutEngine())->layout($document);
        $renderer = new Pdf1_7Renderer(null, false);
        $pdf = $renderer->render($document, $layout);

        self::assertStringStartsWith('%PDF-1.7', $pdf);
    }
}
