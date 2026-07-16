<?php

declare(strict_types=1);

namespace Folio\Pdf\Ports;

use Folio\Pdf\StyleEngine\StyleSheet;

interface StyleParserPort
{
    public function parse(string $source): StyleSheet;
}
