<?php

declare(strict_types=1);

namespace Folio\Pdf\Tests\Template;

use Folio\Pdf\Template\Error\TemplateError;
use Folio\Pdf\Template\Lexer;
use Folio\Pdf\Template\TokenType;
use PHPUnit\Framework\TestCase;

final class LexerTest extends TestCase
{
    public function testTokenizeIdentifier(): void
    {
        $lexer = new Lexer('hello');
        $tokens = $lexer->tokenize();

        $this->assertCount(1, $tokens);
        $this->assertEquals(TokenType::Identifier, $tokens[0]->type);
        $this->assertEquals('hello', $tokens[0]->value);
    }

    public function testTokenizeString(): void
    {
        $lexer = new Lexer('"hello world"');
        $tokens = $lexer->tokenize();

        $this->assertCount(1, $tokens);
        $this->assertEquals(TokenType::String, $tokens[0]->type);
        $this->assertEquals('hello world', $tokens[0]->value);
    }

    public function testTokenizeNumber(): void
    {
        $lexer = new Lexer('123');
        $tokens = $lexer->tokenize();

        $this->assertCount(1, $tokens);
        $this->assertEquals(TokenType::Number, $tokens[0]->type);
        $this->assertEquals('123', $tokens[0]->value);
    }

    public function testTokenizeComment(): void
    {
        $lexer = new Lexer('// this is a comment');
        $tokens = $lexer->tokenize();

        $this->assertCount(1, $tokens);
        $this->assertEquals(TokenType::Comment, $tokens[0]->type);
    }

    public function testTokenizeKeywords(): void
    {
        $lexer = new Lexer('page column text');
        $tokens = $lexer->tokenize();

        $this->assertCount(3, $tokens);
        $this->assertEquals(TokenType::Keyword, $tokens[0]->type);
        $this->assertEquals('page', $tokens[0]->value);
    }

    public function testTokenizeDirective(): void
    {
        $lexer = new Lexer('@header');
        $tokens = $lexer->tokenize();

        $this->assertCount(1, $tokens);
        $this->assertEquals(TokenType::Directive, $tokens[0]->type);
        $this->assertEquals('@header', $tokens[0]->value);
    }

    public function testLineColTrackingSingleLine(): void
    {
        $lexer = new Lexer('page column');
        $tokens = $lexer->tokenize();

        $this->assertEquals(1, $tokens[0]->line);
        $this->assertEquals(1, $tokens[0]->column);
        $this->assertEquals(1, $tokens[1]->line);
        $this->assertEquals(6, $tokens[1]->column);
    }

    public function testLineColTrackingMultiLine(): void
    {
        $lexer = new Lexer("page {\n  text \"hi\"\n}");
        $tokens = $lexer->tokenize();

        $this->assertEquals(1, $tokens[0]->line);
        $this->assertEquals(1, $tokens[0]->column);

        $this->assertEquals(1, $tokens[1]->line);
        $this->assertEquals(6, $tokens[1]->column);

        $this->assertEquals(2, $tokens[2]->line);
        $this->assertEquals(3, $tokens[2]->column);

        $this->assertEquals(2, $tokens[3]->line);
        $this->assertEquals(8, $tokens[3]->column);

        $this->assertEquals(3, $tokens[4]->line);
        $this->assertEquals(1, $tokens[4]->column);
    }

    public function testThrowsOnUnknownChar(): void
    {
        $this->expectException(TemplateError::class);
        $this->expectExceptionMessage("Unexpected character '#'");

        $lexer = new Lexer('page # oops');
        $lexer->tokenize();
    }

    public function testThrowsOnUnterminatedString(): void
    {
        $this->expectException(TemplateError::class);
        $this->expectExceptionMessage('Unterminated string literal');

        $lexer = new Lexer('"hello world');
        $lexer->tokenize();
    }

    public function testErrorHasLineCol(): void
    {
        try {
            $lexer = new Lexer("page {\n  # oops\n}");
            $lexer->tokenize();
            $this->fail('Expected TemplateError');
        } catch (TemplateError $e) {
            $this->assertEquals(2, $e->sourceLine);
            $this->assertEquals(3, $e->sourceColumn);
        }
    }

    public function testBooleanOperatorKeywords(): void
    {
        $lexer = new Lexer('and or not');
        $tokens = $lexer->tokenize();

        $this->assertCount(3, $tokens);
        $this->assertEquals(TokenType::Keyword, $tokens[0]->type);
        $this->assertEquals('and', $tokens[0]->value);
        $this->assertEquals('or', $tokens[1]->value);
        $this->assertEquals('not', $tokens[2]->value);
    }

    public function testEmptyKeyword(): void
    {
        $lexer = new Lexer('empty');
        $tokens = $lexer->tokenize();

        $this->assertCount(1, $tokens);
        $this->assertEquals(TokenType::Keyword, $tokens[0]->type);
        $this->assertEquals('empty', $tokens[0]->value);
    }
}
