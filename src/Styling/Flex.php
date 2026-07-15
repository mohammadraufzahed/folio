<?php

declare(strict_types=1);

namespace Folio\Pdf\Styling;

enum Flex: string
{
    case Row = 'row';
    case Column = 'column';
    case RowReverse = 'row-reverse';
    case ColumnReverse = 'column-reverse';
}
