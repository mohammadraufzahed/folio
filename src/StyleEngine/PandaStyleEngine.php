<?php

declare(strict_types=1);

namespace Folio\Pdf\StyleEngine;

use Folio\Pdf\Contracts\Node;
use Folio\Pdf\Styling\Style;

final readonly class PandaStyleEngine implements StyleEngine
{
    public function resolve(Node $node, StyleContext $context): ComputedStyle
    {
        $style = $node->style();

        if ($style === null) {
            $style = Style::make();
        }

        return new ComputedStyle(
            new BoxStyle(
                padding: $style->padding(),
                margin: $style->margin(),
                border: $style->border(),
                radius: $style->radius(),
                background: $style->background(),
                shadow: $style->shadow(),
                width: $style->width(),
                height: $style->height(),
            ),
            new TextStyle(
                font: $style->font(),
                fontSize: $style->fontSize(),
                fontWeight: $style->fontWeight(),
                color: $style->color(),
                lineHeight: $style->lineHeight(),
                letterSpacing: $style->letterSpacing(),
                alignment: $style->alignment(),
            ),
            new LayoutStyle(
                width: $style->width(),
                height: $style->height(),
                minWidth: $style->minWidth(),
                maxWidth: $style->maxWidth(),
                grow: $style->grow(),
                shrink: $style->shrink(),
            ),
            new PaintStyle(
                fill: $style->background(),
                opacity: $style->opacity(),
            ),
        );
    }
}
