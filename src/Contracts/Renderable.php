<?php

declare(strict_types=1);

namespace Folio\Pdf\Contracts;

use Folio\Pdf\Renderer\RenderContext;

/**
 * Interface for objects that can be rendered to PDF.
 */
interface Renderable
{
    /**
     * Render this object to PDF using the given context.
     */
    public function render(RenderContext $context): void;
}
