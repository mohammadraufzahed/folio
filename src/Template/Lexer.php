<?php

declare(strict_types=1);

namespace Folio\Pdf\Template;

use Folio\Pdf\Template\Error\TemplateError;

/**
 * Lexer for the Folio template language.
 *
 * Produces tokens with 1-based line and column for diagnostics.
 * Throws TemplateError on unterminated strings and unknown characters.
 */
final class Lexer
{
    private int $position = 0;
    private int $line = 1;
    private int $column = 1;
    private string $input;
    private int $length;

    public function __construct(string $input)
    {
        $this->input = $input;
        $this->length = strlen($input);
    }

    /**
     * Tokenize the input string.
     *
     * @return array<int, Token>
     */
    public function tokenize(): array
    {
        $tokens = [];

        while ($this->position < $this->length) {
            $token = $this->nextToken();
            if ($token !== null) {
                $tokens[] = $token;
            }
        }

        return $tokens;
    }

    private function nextToken(): ?Token
    {
        $char = $this->currentChar();

        if (ctype_space($char)) {
            $this->advance();
            return null;
        }

        if ($char === '/' && $this->peekChar() === '/') {
            return $this->lexComment();
        }

        if (ctype_alpha($char) || $char === '_') {
            return $this->lexIdentifier();
        }

        if ($char === '"' || $char === "'") {
            return $this->lexString();
        }

        if (ctype_digit($char)) {
            return $this->lexNumber();
        }
        return match ($char) {
            '{' => $this->makeToken(TokenType::LeftBrace, '{'),
            '}' => $this->makeToken(TokenType::RightBrace, '}'),
            '(' => $this->makeToken(TokenType::LeftParen, '('),
            ')' => $this->makeToken(TokenType::RightParen, ')'),
            '[' => $this->makeToken(TokenType::LeftBracket, '['),
            ']' => $this->makeToken(TokenType::RightBracket, ']'),
            '@' => $this->lexAtSymbol(),
            ',' => $this->makeToken(TokenType::Comma, ','),
            '.' => $this->makeToken(TokenType::Dot, '.'),
            '=' => $this->lexEquals(),
            '!' => $this->lexBang(),
            '<' => $this->lexLessThan(),
            '>' => $this->lexGreaterThan(),
            default => throw new TemplateError(
                "Unexpected character '{$char}'",
                $this->line,
                $this->column,
                1,
            ),
        };
    }

    private function lexComment(): Token
    {
        $start = $this->position;
        $startLine = $this->line;
        $startCol = $this->column;
        $this->advance();
        $this->advance();

        while ($this->position < $this->length && $this->currentChar() !== "\n") {
            $this->advance();
        }

        $value = substr($this->input, $start, $this->position - $start);
        return new Token(
            TokenType::Comment,
            $value,
            $start,
            $startLine,
            $startCol,
            strlen($value),
        );
    }

    private function lexIdentifier(): Token
    {
        $start = $this->position;
        $startLine = $this->line;
        $startCol = $this->column;

        while ($this->position < $this->length) {
            $char = $this->currentChar();
            if (!ctype_alnum($char) && $char !== '_') {
                break;
            }
            $this->advance();
        }

        $value = substr($this->input, $start, $this->position - $start);
        $type = $this->getKeywordType($value);

        return new Token($type, $value, $start, $startLine, $startCol, strlen($value));
    }

    private function getKeywordType(string $value): TokenType
    {
        return match ($value) {
            'page', 'column', 'row', 'text', 'heading',
            'table', 'tr', 'th', 'td', 'header', 'footer' => TokenType::Keyword,

            'if', 'else', 'elseif', 'foreach', 'as', 'empty' => TokenType::Keyword,

            'var', 'prop', 'partial', 'pageheader', 'pagefooter' => TokenType::Keyword,

            'monogram', 'badge', 'spacer', 'rule', 'box', 'pagenum', 'img' => TokenType::Keyword,

            'and', 'or', 'not' => TokenType::Keyword,

            default => TokenType::Identifier,
        };
    }

    private function lexString(): Token
    {
        $quote = $this->currentChar();
        $start = $this->position;
        $startLine = $this->line;
        $startCol = $this->column;
        $this->advance();

        $value = '';
        while ($this->position < $this->length) {
            $char = $this->currentChar();

            if ($char === '\\') {
                $this->advance();
                if ($this->position >= $this->length) {
                    break;
                }
                $value .= $this->currentChar();
                $this->advance();
                continue;
            }

            if ($char === $quote) {
                $this->advance();
                return new Token(
                    TokenType::String,
                    $value,
                    $start,
                    $startLine,
                    $startCol,
                    $this->position - $start,
                );
            }

            $value .= $char;
            $this->advance();
        }

        throw new TemplateError(
            'Unterminated string literal',
            $startLine,
            $startCol,
            $this->position - $start,
        );
    }

    private function lexNumber(): Token
    {
        $start = $this->position;
        $startLine = $this->line;
        $startCol = $this->column;

        while ($this->position < $this->length && ctype_digit($this->currentChar())) {
            $this->advance();
        }

        if ($this->position < $this->length && $this->currentChar() === '.') {
            $this->advance();
            while ($this->position < $this->length && ctype_digit($this->currentChar())) {
                $this->advance();
            }
        }

        $value = substr($this->input, $start, $this->position - $start);
        return new Token(TokenType::Number, $value, $start, $startLine, $startCol, strlen($value));
    }

    private function lexAtSymbol(): Token
    {
        $start = $this->position;
        $startLine = $this->line;
        $startCol = $this->column;
        $this->advance();

        if ($this->position < $this->length && ctype_alpha($this->currentChar())) {
            while ($this->position < $this->length && ctype_alnum($this->currentChar())) {
                $this->advance();
            }
            $value = substr($this->input, $start, $this->position - $start);
            return new Token(TokenType::Directive, $value, $start, $startLine, $startCol, strlen($value));
        }

        return $this->makeToken(TokenType::At, '@');
    }

    private function lexEquals(): Token
    {
        $start = $this->position;
        $startLine = $this->line;
        $startCol = $this->column;
        $this->advance();

        if ($this->position < $this->length && $this->currentChar() === '=') {
            $this->advance();
            return new Token(TokenType::EqualsEquals, '==', $start, $startLine, $startCol, 2);
        }

        return new Token(TokenType::Equals, '=', $start, $startLine, $startCol, 1);
    }

    private function lexBang(): Token
    {
        $start = $this->position;
        $startLine = $this->line;
        $startCol = $this->column;
        $this->advance();

        if ($this->position < $this->length && $this->currentChar() === '=') {
            $this->advance();
            return new Token(TokenType::NotEquals, '!=', $start, $startLine, $startCol, 2);
        }

        return new Token(TokenType::Bang, '!', $start, $startLine, $startCol, 1);
    }

    private function lexLessThan(): Token
    {
        $start = $this->position;
        $startLine = $this->line;
        $startCol = $this->column;
        $this->advance();

        if ($this->position < $this->length && $this->currentChar() === '=') {
            $this->advance();
            return new Token(TokenType::LessThanOrEqual, '<=', $start, $startLine, $startCol, 2);
        }

        return new Token(TokenType::LessThan, '<', $start, $startLine, $startCol, 1);
    }

    private function lexGreaterThan(): Token
    {
        $start = $this->position;
        $startLine = $this->line;
        $startCol = $this->column;
        $this->advance();

        if ($this->position < $this->length && $this->currentChar() === '=') {
            $this->advance();
            return new Token(TokenType::GreaterThanOrEqual, '>=', $start, $startLine, $startCol, 2);
        }

        return new Token(TokenType::GreaterThan, '>', $start, $startLine, $startCol, 1);
    }

    private function currentChar(): string
    {
        return $this->position < $this->length ? $this->input[$this->position] : '';
    }

    private function peekChar(): string
    {
        $next = $this->position + 1;
        return $next < $this->length ? $this->input[$next] : '';
    }

    private function advance(): void
    {
        if ($this->position < $this->length) {
            if ($this->input[$this->position] === "\n") {
                $this->line++;
                $this->column = 1;
            } else {
                $this->column++;
            }
            $this->position++;
        }
    }

    private function makeToken(TokenType $type, string $value): Token
    {
        $token = new Token($type, $value, $this->position, $this->line, $this->column, 1);
        $this->advance();
        return $token;
    }
}
