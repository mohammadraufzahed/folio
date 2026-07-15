<?php

declare(strict_types=1);

namespace Folio\Pdf\Tests\Template;

use Folio\Pdf\Template\PhpTemplateCompiler;
use PHPUnit\Framework\TestCase;

final class CompilerTest extends TestCase
{
    private function compile(string $template): string
    {
        $compiler = new PhpTemplateCompiler(sys_get_temp_dir() . '/folio-test-' . uniqid());
        return $compiler->compile($template);
    }

    public function testCompileSimplePage(): void
    {
        $php = $this->compile('page { text "Hello World" }');

        $this->assertStringContainsString('Page::a4()', $php);
        $this->assertStringContainsString('Text::make', $php);
        $this->assertStringContainsString('Hello World', $php);
    }

    public function testCompileComparisonInIf(): void
    {
        $php = $this->compile('if count > 5 { text "big" }');

        $this->assertStringContainsString('>', $php);
        $this->assertStringContainsString('count', $php);
    }

    public function testCompileAndOrInIf(): void
    {
        $php = $this->compile('if a and b { text "both" }');
        $this->assertStringContainsString('&&', $php);

        $php = $this->compile('if a or b { text "either" }');
        $this->assertStringContainsString('||', $php);
    }

    public function testCompileNotInIf(): void
    {
        $php = $this->compile('if not active { text "inactive" }');
        $this->assertStringContainsString('!', $php);
    }

    public function testCompileElseIfChain(): void
    {
        $php = $this->compile('if x > 5 { text "big" } else if x > 0 { text "small" } else { text "zero" }');

        $this->assertStringContainsString('?', $php);
        $this->assertStringContainsString('big', $php);
        $this->assertStringContainsString('small', $php);
        $this->assertStringContainsString('zero', $php);
    }

    public function testCompileForeachWithIndex(): void
    {
        $php = $this->compile('foreach items as i, item { text item.name }');

        $this->assertStringContainsString('$__key', $php);
        $this->assertStringContainsString('$__val', $php);
        $this->assertStringContainsString("'i' => \$__key", $php);
        $this->assertStringContainsString("'item' => \$__val", $php);
    }

    public function testCompileForeachWithEmpty(): void
    {
        $php = $this->compile('page { foreach items as item { text item.name } empty { text "No items" } }');

        $this->assertStringContainsString('$__out === []', $php);
        $this->assertStringContainsString('No items', $php);
    }

    public function testCompileVarDefaults(): void
    {
        $php = $this->compile('var title = "Default Title" page { text title }');

        $this->assertStringContainsString('array_merge', $php);
        $this->assertStringContainsString('Default Title', $php);
    }

    public function testCompileAttributes(): void
    {
        $php = $this->compile('td(variant=success) "Done"');

        $this->assertStringContainsString('success', $php);
    }

    public function testCompilePropertyAccess(): void
    {
        $php = $this->compile('page { text company.name }');

        $this->assertStringContainsString('Scope', $php);
        $this->assertStringContainsString('company', $php);
        $this->assertStringContainsString('name', $php);
    }

    public function testCompileGroupedExpression(): void
    {
        $php = $this->compile('if (a or b) and c { text "complex" }');

        $this->assertStringContainsString('||', $php);
        $this->assertStringContainsString('&&', $php);
    }

    public function testStrictModeEmitsTrueFlag(): void
    {
        $compiler = new PhpTemplateCompiler(sys_get_temp_dir() . '/folio-test-' . uniqid());
        $compiler->setStrict(true);
        $php = $compiler->compile('page { text name }');

        $this->assertStringContainsString('true', $php);
        $this->assertStringContainsString('Scope', $php);
    }

    public function testNonStrictModeEmitsFalseFlag(): void
    {
        $php = $this->compile('page { text name }');

        $this->assertStringContainsString('false', $php);
    }

    public function testCompilePageWithSizeA3(): void
    {
        $php = $this->compile('page(size=a3) { text "hi" }');

        $this->assertStringContainsString('Page::a3()', $php);
    }

    public function testCompilePageWithSizeLetter(): void
    {
        $php = $this->compile('page(size=letter) { text "hi" }');

        $this->assertStringContainsString('Page::letter()', $php);
    }

    public function testCompilePageWithCustomSize(): void
    {
        $php = $this->compile('page(size="600x800") { text "hi" }');

        $this->assertStringContainsString('Page::make(600, 800)', $php);
    }

    public function testCompilePageLandscape(): void
    {
        $php = $this->compile('page(orientation=landscape) { text "hi" }');

        $this->assertStringContainsString('842', $php);
        $this->assertStringContainsString('595', $php);
    }

    public function testCompilePageWithStyleAttrs(): void
    {
        $php = $this->compile('text(color=red, fontSize=14) "hi"');

        $this->assertStringContainsString('AttributeMapper', $php);
        $this->assertStringContainsString("'color'", $php);
        $this->assertStringContainsString("'red'", $php);
    }

    public function testCompilePartialInlinesContent(): void
    {
        $compiler = new PhpTemplateCompiler(sys_get_temp_dir() . '/folio-test-' . uniqid());
        $compiler->setBaseDir(__DIR__ . '/fixtures');
        $php = $compiler->compile('page { partial "test-partial" }');

        $this->assertStringContainsString('Partial Content', $php);
        $this->assertStringNotContainsString('partial not found', $php);
    }

    public function testCompilePartialNotFound(): void
    {
        $compiler = new PhpTemplateCompiler(sys_get_temp_dir() . '/folio-test-' . uniqid());
        $php = $compiler->compile('page { partial "nonexistent" }');

        $this->assertStringContainsString('partial not found', $php);
    }
}
