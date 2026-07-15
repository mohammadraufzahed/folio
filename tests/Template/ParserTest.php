<?php

declare(strict_types=1);

namespace Folio\Pdf\Tests\Template;

use Folio\Pdf\Template\AstNode;
use Folio\Pdf\Template\Error\TemplateError;
use Folio\Pdf\Template\Lexer;
use Folio\Pdf\Template\Parser;
use PHPUnit\Framework\TestCase;

final class ParserTest extends TestCase
{
    private function parse(string $source): AstNode
    {
        $lexer = new Lexer($source);
        $parser = new Parser($lexer->tokenize());

        return $parser->parse();
    }

    public function testParseSimplePage(): void
    {
        $ast = $this->parse('page { text "Hello" }');

        $this->assertEquals('Document', $ast->type);
        $this->assertCount(1, $ast->children);
        $page = $ast->children[0];
        $this->assertEquals('Element', $page->type);
        $this->assertEquals('page', $page->attributes['type']);
    }

    public function testParseComparisonExpression(): void
    {
        $ast = $this->parse('if count > 5 { text "big" }');

        $ifNode = $ast->children[0];
        $this->assertEquals('If', $ifNode->type);
        $cond = $ifNode->attributes['condition'];
        $this->assertEquals('BinaryOp', $cond->type);
        $this->assertEquals('>', $cond->attributes['op']);
    }

    public function testParseEqualityExpression(): void
    {
        $ast = $this->parse('if status == "active" { text "yes" }');

        $cond = $ast->children[0]->attributes['condition'];
        $this->assertEquals('BinaryOp', $cond->type);
        $this->assertEquals('==', $cond->attributes['op']);
    }

    public function testParseAndOrExpressions(): void
    {
        $ast = $this->parse('if a and b { text "both" }');
        $cond = $ast->children[0]->attributes['condition'];
        $this->assertEquals('BinaryOp', $cond->type);
        $this->assertEquals('and', $cond->attributes['op']);

        $ast = $this->parse('if a or b { text "either" }');
        $cond = $ast->children[0]->attributes['condition'];
        $this->assertEquals('BinaryOp', $cond->type);
        $this->assertEquals('or', $cond->attributes['op']);
    }

    public function testParseNotExpression(): void
    {
        $ast = $this->parse('if not active { text "inactive" }');
        $cond = $ast->children[0]->attributes['condition'];
        $this->assertEquals('UnaryOp', $cond->type);
        $this->assertEquals('not', $cond->attributes['op']);
    }

    public function testParseBangExpression(): void
    {
        $ast = $this->parse('if !active { text "inactive" }');
        $cond = $ast->children[0]->attributes['condition'];
        $this->assertEquals('UnaryOp', $cond->type);
        $this->assertEquals('not', $cond->attributes['op']);
    }

    public function testParseGroupedExpression(): void
    {
        $ast = $this->parse('if (a or b) and c { text "complex" }');
        $cond = $ast->children[0]->attributes['condition'];
        $this->assertEquals('BinaryOp', $cond->type);
        $this->assertEquals('and', $cond->attributes['op']);

        $left = $cond->children[0];
        $this->assertEquals('BinaryOp', $left->type);
        $this->assertEquals('or', $left->attributes['op']);
    }

    public function testParseElseIfChain(): void
    {
        $ast = $this->parse('if x > 5 { text "big" } else if x > 0 { text "small" } else { text "zero" }');

        $ifNode = $ast->children[0];
        $this->assertEquals('If', $ifNode->type);

        $this->assertCount(2, $ifNode->children);
        $this->assertEquals('Block', $ifNode->children[0]->type);
        $this->assertEquals('If', $ifNode->children[1]->type);

        $nestedIf = $ifNode->children[1];
        $this->assertCount(2, $nestedIf->children);
        $this->assertEquals('Block', $nestedIf->children[0]->type);
        $this->assertEquals('Block', $nestedIf->children[1]->type);
    }

    public function testParseElseifKeywordChain(): void
    {
        $ast = $this->parse('if x > 5 { text "big" } elseif x > 0 { text "small" } else { text "zero" }');

        $ifNode = $ast->children[0];
        $this->assertEquals('If', $ifNode->type);

        $this->assertCount(2, $ifNode->children);
        $this->assertEquals('If', $ifNode->children[1]->type);
    }

    public function testParseForeachWithIndex(): void
    {
        $ast = $this->parse('foreach items as i, item { text item.name }');

        $foreachNode = $ast->children[0];
        $this->assertEquals('Foreach', $foreachNode->type);
        $this->assertEquals('i', $foreachNode->attributes['index']);
        $this->assertEquals('item', $foreachNode->attributes['item']);
    }

    public function testParseForeachWithEmpty(): void
    {
        $ast = $this->parse('foreach items as item { text item.name } empty { text "No items" }');

        $foreachNode = $ast->children[0];
        $this->assertEquals('Foreach', $foreachNode->type);
        $this->assertTrue($foreachNode->attributes['hasEmpty']);
        $this->assertCount(2, $foreachNode->children);
    }

    public function testParseVarDecl(): void
    {
        $ast = $this->parse('var title = "Report"');

        $varNode = $ast->children[0];
        $this->assertEquals('VarDecl', $varNode->type);
        $this->assertEquals('title', $varNode->attributes['name']);
        $this->assertEquals('var', $varNode->attributes['keyword']);
    }

    public function testParseAttributes(): void
    {
        $ast = $this->parse('td(variant=success, colspan=2) "Done"');

        $tdNode = $ast->children[0];
        $this->assertEquals('Element', $tdNode->type);
        $this->assertEquals('td', $tdNode->attributes['type']);
        $this->assertEquals('success', $tdNode->attributes['attributes']['variant']);
        $this->assertEquals('2', $tdNode->attributes['attributes']['colspan']);
    }

    public function testThrowsOnUnexpectedToken(): void
    {
        $this->expectException(TemplateError::class);
        $this->parse('page { } }');
    }

    public function testThrowsOnMissingBrace(): void
    {
        $this->expectException(TemplateError::class);
        $this->expectExceptionMessage("Expected '}'");

        $this->parse('page { text "hi"');
    }

    public function testThrowsOnUnknownKeyword(): void
    {
        $this->expectException(TemplateError::class);

        $this->parse('page { ] }');
    }

    public function testErrorHasLineCol(): void
    {
        try {
            $this->parse("page {\n  text \"hi\"\n  ]\n}");
            $this->fail('Expected TemplateError');
        } catch (TemplateError $e) {
            $this->assertEquals(3, $e->sourceLine);
            $this->assertEquals(3, $e->sourceColumn);
        }
    }
}
