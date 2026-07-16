<?php

declare(strict_types=1);

namespace Folio\Pdf\Tests\Template;

use Folio\Pdf\Template\TemplateEngine;
use PHPUnit\Framework\TestCase;

final class TemplateEngineTest extends TestCase
{
    public function testRendersTemplateToPdf(): void
    {
        $engine = new TemplateEngine();
        $pdf = $engine->render('page { text "Hello from v2" }');

        self::assertStringStartsWith('%PDF-1.7', $pdf);
    }

    public function testRendersTemplateWithVariables(): void
    {
        $engine = new TemplateEngine();
        $pdf = $engine->render('var name = "Folio" page { text name }', ['name' => 'World']);

        self::assertStringStartsWith('%PDF-1.7', $pdf);
    }
}
