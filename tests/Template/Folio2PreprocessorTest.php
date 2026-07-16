<?php

declare(strict_types=1);

namespace Folio\Pdf\Tests\Template;

use Folio\Pdf\Template\Folio2Preprocessor;
use PHPUnit\Framework\TestCase;

final class Folio2PreprocessorTest extends TestCase
{
    public function testRewritesPropDeclarations(): void
    {
        $preprocessor = new Folio2Preprocessor();

        self::assertSame(
            'var name = ""',
            $preprocessor->process('prop name: string'),
        );

        self::assertSame(
            'var invoice = ""',
            $preprocessor->process('prop invoice: Invoice'),
        );
    }

    public function testRewritesPropWithDefault(): void
    {
        $preprocessor = new Folio2Preprocessor();

        self::assertSame(
            'var name = "Folio"',
            $preprocessor->process('prop name: string = "Folio"'),
        );
    }

    public function testInlinesUseDirective(): void
    {
        $dir = sys_get_temp_dir() . '/folio-preprocessor-' . uniqid();
        mkdir($dir);
        file_put_contents($dir . '/header.folio', 'text "Header"');

        $preprocessor = new Folio2Preprocessor($dir);
        $output = $preprocessor->process('@use "header.folio"');

        self::assertStringContainsString('text "Header"', $output);
    }

    public function testIgnoresThemeDirective(): void
    {
        $preprocessor = new Folio2Preprocessor();

        self::assertSame('', $preprocessor->process('@theme "corporate"'));
    }
}
