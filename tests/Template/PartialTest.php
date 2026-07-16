<?php

declare(strict_types=1);

namespace Folio\Pdf\Tests\Template;

use Folio\Pdf\Layout\LayoutContext;
use Folio\Pdf\Layout\LayoutEngine;
use Folio\Pdf\Nodes\Column;
use Folio\Pdf\Nodes\Text;
use Folio\Pdf\Template\Component;
use Folio\Pdf\Template\Partial;
use Folio\Pdf\Template\PartialRegistry;
use Folio\Pdf\Template\Slot;
use PHPUnit\Framework\TestCase;

final class PartialTest extends TestCase
{
    public function testPartialReplacesSlots(): void
    {
        $partial = new Partial(
            'card',
            Column::make(null, [
                new Slot('header'),
                Text::make('Default body'),
            ]),
            slots: ['header' => Text::make('Default header')],
        );

        $registry = new PartialRegistry();
        $registry->register($partial);

        $result = $registry->resolve(
            new Component('card', slots: ['header' => Text::make('Custom header')])
        );

        self::assertInstanceOf(Column::class, $result);
        self::assertInstanceOf(Text::class, $result->children()[0]);
        self::assertSame('Custom header', $result->children()[0]->text());
    }

    public function testComponentLayoutResolvesPartial(): void
    {
        $partial = new Partial(
            'signature',
            Column::make(null, [
                Text::make('____________________'),
                new Slot('date'),
            ]),
            slots: ['date' => Text::make('Default date')],
        );

        $registry = new PartialRegistry();
        $registry->register($partial);

        $engine = new LayoutEngine(partials: $registry);
        $component = new Component('signature', slots: ['date' => Text::make('July 2026')]);
        $box = $engine->layoutNode($component, LayoutContext::make(200.0, 500.0));

        self::assertGreaterThan(0.0, $box->height());
    }

    public function testMissingPartialThrows(): void
    {
        $registry = new PartialRegistry();

        $this->expectException(\InvalidArgumentException::class);

        $registry->resolve(new Component('unknown'));
    }
}
