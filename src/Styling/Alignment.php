<?php

declare(strict_types=1);

namespace Folio\Pdf\Styling;

/**
 * Alignment enumeration.
 */
enum Alignment: string
{
    case Left = 'left';
    case Center = 'center';
    case Right = 'right';
    case Justify = 'justify';
    case Top = 'top';
    case Middle = 'middle';
    case Bottom = 'bottom';
}
