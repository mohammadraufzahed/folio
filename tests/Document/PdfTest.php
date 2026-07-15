<?php

declare(strict_types=1);

namespace Folio\Pdf\Tests\Document;

use Folio\Pdf\Document\Pdf;
use Folio\Pdf\Nodes\Page;
use Folio\Pdf\Nodes\Text;
use PHPUnit\Framework\TestCase;

final class PdfTest extends TestCase
{
    public function testMake(): void
    {
        $pdf = Pdf::make();
        $this->assertInstanceOf(Pdf::class, $pdf);
    }

    public function testTheme(): void
    {
        $pdf1 = Pdf::make();
        $pdf2 = $pdf1->theme('navy');

        $this->assertNotSame($pdf1, $pdf2);
    }

    public function testPage(): void
    {
        $pdf = Pdf::make()->page(Page::a4());

        $this->assertInstanceOf(Pdf::class, $pdf);
    }

    public function testContent(): void
    {
        $content = Text::make('Test');
        $pdf = Pdf::make()->content($content);

        $this->assertInstanceOf(Pdf::class, $pdf);
    }

    public function testImmutability(): void
    {
        $pdf1 = Pdf::make();
        $pdf2 = $pdf1->theme('navy');

        $this->assertNotSame($pdf1, $pdf2);
    }

    public function testChaining(): void
    {
        $pdf = Pdf::make()
            ->theme('navy')
            ->page(Page::a4());

        $this->assertInstanceOf(Pdf::class, $pdf);
    }
}
