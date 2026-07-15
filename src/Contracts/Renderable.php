<?php

declare(strict_types=1);

namespace Folio\Pdf\Contracts;

use Folio\Pdf\Renderer\RenderContext;

interface Renderable
{
    public function render(RenderContext $context): void;
}
