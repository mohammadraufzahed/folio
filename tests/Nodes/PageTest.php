<?php

declare(strict_types=1);

namespace Folio\Pdf\Tests\Nodes;

use Folio\Pdf\Nodes\Page;
use Folio\Pdf\Nodes\Text;
use PHPUnit\Framework\TestCase;

final class PageTest extends TestCase
{
    public function testMake(): void
    {
        $page = Page::make();
        $this->assertEquals(595.0, $page->width());
        $this->assertEquals(842.0, $page->height());
        $this->assertNull($page->content());
    }

    public function testA4(): void
    {
        $page = Page::a4();
        $this->assertEquals(595.0, $page->width());
        $this->assertEquals(842.0, $page->height());
    }

    public function testLetter(): void
    {
        $page = Page::letter();
        $this->assertEquals(612.0, $page->width());
        $this->assertEquals(792.0, $page->height());
    }

    public function testA3(): void
    {
        $page = Page::a3();
        $this->assertEquals(842.0, $page->width());
        $this->assertEquals(1191.0, $page->height());
    }

    public function testWithContent(): void
    {
        $text = Text::make('Hello');
        $page = Page::make()->withContent($text);

        $this->assertSame($text, $page->content());
    }

    public function testWithSize(): void
    {
        $page = Page::make()->withSize(100.0, 200.0);
        $this->assertEquals(100.0, $page->width());
        $this->assertEquals(200.0, $page->height());
    }

    public function testImmutability(): void
    {
        $page1 = Page::make();
        $page2 = $page1->withContent(Text::make('Hello'));

        $this->assertNotSame($page1, $page2);
        $this->assertNull($page1->content());
        $this->assertNotNull($page2->content());
    }
}
