<?php

declare(strict_types=1);

namespace Folio\Pdf\Styling;

enum BorderStyle: string
{
    case Solid = 'solid';
    case Dashed = 'dashed';
    case Dotted = 'dotted';
    case Double = 'double';
    case None = 'none';
}
