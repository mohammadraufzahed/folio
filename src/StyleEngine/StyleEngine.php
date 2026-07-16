<?php

declare(strict_types=1);

namespace Folio\Pdf\StyleEngine;

use Folio\Pdf\Contracts\Node;

interface StyleEngine
{
    public function resolve(Node $node, StyleContext $context): ComputedStyle;
}
