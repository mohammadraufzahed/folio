<?php

declare(strict_types=1);

namespace Folio\Pdf\Styling;

enum FontWeight: int
{
    case Thin = 100;
    case ExtraLight = 200;
    case Light = 300;
    case Regular = 400;
    case Medium = 500;
    case SemiBold = 600;
    case Bold = 700;
    case ExtraBold = 800;
    case Black = 900;
}
