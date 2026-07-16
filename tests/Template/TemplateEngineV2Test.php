<?php

declare(strict_types=1);

namespace Folio\Pdf\Tests\Template;

use Folio\Pdf\Template\TemplateEngine;
use PHPUnit\Framework\TestCase;

final class TemplateEngineV2Test extends TestCase
{
    public function testRendersPropSyntax(): void
    {
        $engine = (new TemplateEngine())->enableFolio2Syntax();
        $pdf = $engine->render("prop name: string = \"Folio\"\npage { text name }", ['name' => 'World']);

        self::assertStringStartsWith('%PDF-1.7', $pdf);
    }

    public function testRendersFileWithUseDirective(): void
    {
        $dir = sys_get_temp_dir() . '/folio-engine-v2-' . uniqid();
        mkdir($dir);
        file_put_contents($dir . '/header.folio', 'text "Included"');
        file_put_contents($dir . '/main.folio', '@use "header.folio"' . "\n" . 'page { column { heading "Main" } }');

        $engine = (new TemplateEngine())->enableFolio2Syntax();
        $pdf = $engine->renderFile($dir . '/main.folio');

        self::assertStringStartsWith('%PDF-1.7', $pdf);
    }
}
