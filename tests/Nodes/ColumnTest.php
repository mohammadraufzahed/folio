<?php

declare(strict_types=1);

namespace Folio\Pdf\Tests\Nodes;

use Folio\Pdf\Nodes\Column;
use Folio\Pdf\Nodes\Text;
use Folio\Pdf\Styling\Style;
use PHPUnit\Framework\TestCase;

final class ColumnTest extends TestCase
{
    public function testMake(): void
    {
        $column = Column::make();
        $this->assertInstanceOf(Column::class, $column);
        $this->assertFalse($column->hasChildren());
    }

    public function testAddChildren(): void
    {
        $column = Column::make()->addChildren([
            Text::make('First'),
            Text::make('Second'),
        ]);
        
        $this->assertTrue($column->hasChildren());
        $this->assertCount(2, $column->children());
    }

    public function testWithStyle(): void
    {
        $style = Style::make()->withPadding(10.0);
        $column = Column::make()->withStyle($style);
        
        $this->assertSame($style, $column->style());
    }

    public function testImmutability(): void
    {
        $column1 = Column::make();
        $column2 = $column1->addChildren([Text::make('Test')]);
        
        $this->assertNotSame($column1, $column2);
        $this->assertFalse($column1->hasChildren());
        $this->assertTrue($column2->hasChildren());
    }

    public function testChaining(): void
    {
        $style = Style::make()->withPadding(20.0);
        $column = Column::make()
            ->withStyle($style)
            ->addChildren([Text::make('Test')]);
        
        $this->assertSame($style, $column->style());
        $this->assertTrue($column->hasChildren());
    }
}
