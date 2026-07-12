<?php

declare(strict_types=1);

namespace Folio\Pdf\Tests\Template;

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
}
