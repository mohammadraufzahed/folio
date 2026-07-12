<?php

declare(strict_types=1);

namespace Folio\Pdf\Template;

/**
 * Lexer for the custom template language.
 */
final class Lexer
{
    private int $position = 0;
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

        // Skip whitespace
        if (ctype_space($char)) {
            $this->advance();
            return null;
        }

        // Comments
        if ($char === '/' && $this->peekChar() === '/') {
            return $this->lexComment();
        }

        // Identifiers and keywords
        if (ctype_alpha($char) || $char === '_') {
            return $this->lexIdentifier();
        }

        // Strings
        if ($char === '"' || $char === "'") {
            return $this->lexString();
        }

        // Numbers
        if (ctype_digit($char)) {
            return $this->lexNumber();
        }

        // Operators and punctuation
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
            default => $this->makeToken(TokenType::Unknown, $char),
        };
    }

    private function lexComment(): Token
    {
        $start = $this->position;
        $this->advance(); // Skip first /
        $this->advance(); // Skip second /

        while ($this->position < $this->length && $this->currentChar() !== "\n") {
            $this->advance();
        }

        $value = substr($this->input, $start, $this->position - $start);
        return $this->makeToken(TokenType::Comment, $value);
    }

    private function lexIdentifier(): Token
    {
        $start = $this->position;

        while ($this->position < $this->length) {
            $char = $this->currentChar();
            if (!ctype_alnum($char) && $char !== '_') {
                break;
            }
            $this->advance();
        }

        $value = substr($this->input, $start, $this->position - $start);
        $type = $this->getKeywordType($value);

        return new Token($type, $value, $start);
    }

    private function getKeywordType(string $value): TokenType
    {
        return match ($value) {
            'page', 'column', 'row', 'text', 'heading' => TokenType::Keyword,
            'table', 'tr', 'th', 'td', 'header' => TokenType::Keyword,
            'if', 'else', 'elseif', 'endif' => TokenType::Keyword,
            'foreach', 'endforeach', 'as' => TokenType::Keyword,
            'switch', 'case', 'break', 'default', 'endswitch' => TokenType::Keyword,
            'import', 'layout', 'slot', 'endslot' => TokenType::Keyword,
            'component', 'endcomponent' => TokenType::Keyword,
            default => TokenType::Identifier,
        };
    }

    private function lexString(): Token
    {
        $quote = $this->currentChar();
        $start = $this->position;
        $this->advance(); // Skip opening quote

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
                break;
            }

            $value .= $char;
            $this->advance();
        }

        return new Token(TokenType::String, $value, $start);
    }

    private function lexNumber(): Token
    {
        $start = $this->position;

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
        return new Token(TokenType::Number, $value, $start);
    }

    private function lexAtSymbol(): Token
    {
        $start = $this->position;
        $this->advance();

        if ($this->position < $this->length && ctype_alpha($this->currentChar())) {
            while ($this->position < $this->length && ctype_alnum($this->currentChar())) {
                $this->advance();
            }
            $value = substr($this->input, $start, $this->position - $start);
            return new Token(TokenType::Directive, $value, $start);
        }

        return $this->makeToken(TokenType::At, '@');
    }

    private function lexEquals(): Token
    {
        $start = $this->position;
        $this->advance();

        if ($this->position < $this->length && $this->currentChar() === '=') {
            $this->advance();
            return $this->makeToken(TokenType::EqualsEquals, '==', $start);
        }

        return $this->makeToken(TokenType::Equals, '=', $start);
    }

    private function lexBang(): Token
    {
        $start = $this->position;
        $this->advance();

        if ($this->position < $this->length && $this->currentChar() === '=') {
            $this->advance();
            return $this->makeToken(TokenType::NotEquals, '!=', $start);
        }

        return $this->makeToken(TokenType::Bang, '!', $start);
    }

    private function lexLessThan(): Token
    {
        $start = $this->position;
        $this->advance();

        if ($this->position < $this->length && $this->currentChar() === '=') {
            $this->advance();
            return $this->makeToken(TokenType::LessThanOrEqual, '<=', $start);
        }

        return $this->makeToken(TokenType::LessThan, '<', $start);
    }

    private function lexGreaterThan(): Token
    {
        $start = $this->position;
        $this->advance();

        if ($this->position < $this->length && $this->currentChar() === '=') {
            $this->advance();
            return $this->makeToken(TokenType::GreaterThanOrEqual, '>=', $start);
        }

        return $this->makeToken(TokenType::GreaterThan, '>', $start);
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
        $this->position++;
    }

    private function makeToken(TokenType $type, string $value, ?int $position = null): Token
    {
        // When $position is provided, the caller already advanced past the token.
        // When null, consume the current single-character token.
        if ($position === null) {
            $position = $this->position;
            $this->advance();
        }

        return new Token($type, $value, $position);
    }
}
