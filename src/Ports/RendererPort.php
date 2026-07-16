<?php

declare(strict_types=1);

namespace Folio\Pdf\Ports;

use Folio\Pdf\Document\Document;
use Folio\Pdf\Layout\LayoutResult;

interface RendererPort
{
    public function render(Document $document, LayoutResult $layout): string;
}
