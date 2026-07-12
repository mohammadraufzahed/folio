<?php

declare(strict_types=1);

namespace Folio\Pdf\Template;

/**
 * Parser for the custom template language.
 */
final class Parser
{
    private int $position = 0;
    private array $tokens;

    public function __construct(array $tokens)
    {
        $this->tokens = array_values(array_filter(
            $tokens,
            static fn(Token $token): bool => $token->type !== TokenType::Comment
        ));
    }

    public function parse(): AstNode
    {
        return $this->parseDocument();
    }

    private function parseDocument(): AstNode
    {
        $children = [];

        while (!$this->isAtEnd()) {
            $node = $this->parseStatement();
            if ($node !== null) {
                $children[] = $node;
            }
        }

        return new AstNode('Document', $children);
    }

    private function parseStatement(): ?AstNode
    {
        if ($this->isAtEnd()) {
            return null;
        }

        $token = $this->peek();

        return match ($token->type) {
            TokenType::Directive => $this->parseDirective(),
            TokenType::Keyword => $this->parseKeyword(),
            TokenType::LeftBrace => $this->parseBlock(),
            TokenType::String, TokenType::Identifier, TokenType::Number => $this->parseExpression(),
            default => $this->skipUnknown(),
        };
    }

    private function skipUnknown(): ?AstNode
    {
        $this->advance();
        return null;
    }

    private function parseDirective(): AstNode
    {
        $token = $this->advance();

        return new AstNode('Directive', [], ['name' => $token->value]);
    }

    private function parseKeyword(): AstNode
    {
        $token = $this->advance();
        $keyword = $token->value;

        return match ($keyword) {
            'page', 'column', 'row', 'text', 'heading',
            'table', 'tr', 'th', 'td', 'header', 'footer',
            'monogram', 'badge', 'spacer', 'rule', 'box', 'pagenum', 'img' => $this->parseElement($keyword),
            'pageheader', 'pagefooter' => $this->parsePageChrome($keyword),
            'if' => $this->parseIf(),
            'foreach' => $this->parseForeach(),
            'var', 'prop' => $this->parseVarDecl($keyword),
            'partial' => $this->parsePartial(),
            default => new AstNode('Keyword', [], ['value' => $keyword]),
        };
    }

    private function parseElement(string $type): AstNode
    {
        $children = [];
        $attributes = $this->parseAttributes();

        // Inline content: text "Hello", heading title, td row.label (keywords allowed as vars)
        if (
            $this->check(TokenType::String)
            || $this->check(TokenType::Identifier)
            || $this->check(TokenType::Number)
            || $this->check(TokenType::Keyword)
        ) {
            // Only treat keyword as expression if it looks like a value (followed by . or end of element)
            // Always try expression first for dotted paths like row.label / company.name
            $children[] = $this->parseExpression();
        }

        // Block content: element { ... }
        if ($this->match(TokenType::LeftBrace)) {
            while (!$this->check(TokenType::RightBrace) && !$this->isAtEnd()) {
                $child = $this->parseStatement();
                if ($child !== null) {
                    $children[] = $child;
                }
            }
            $this->consume(TokenType::RightBrace, "Expected '}' after {$type} block");
        }

        return new AstNode('Element', $children, ['type' => $type, 'attributes' => $attributes]);
    }

    /**
     * Parse optional (key=value, key=value) attributes.
     *
     * @return array<string, mixed>
     */
    private function parseAttributes(): array
    {
        $attributes = [];

        if (!$this->match(TokenType::LeftParen)) {
            return $attributes;
        }

        while (!$this->check(TokenType::RightParen) && !$this->isAtEnd()) {
            $nameToken = $this->consume(TokenType::Identifier, 'Expected attribute name');
            $this->consume(TokenType::Equals, "Expected '=' after attribute name");

            // Allow string/number literals OR dotted property paths (company.name)
            if (
                $this->check(TokenType::String) || $this->check(TokenType::Number)
                || $this->check(TokenType::Identifier) || $this->check(TokenType::Keyword)
            ) {
                $valueNode = $this->parseExpression();
                // Keep simple scalars as raw values for existing callers (variant=warning)
                if ($valueNode->type === 'StringLiteral' || $valueNode->type === 'NumberLiteral') {
                    $attributes[$nameToken->value] = $valueNode->attributes['value'] ?? '';
                } elseif ($valueNode->type === 'Identifier') {
                    $attributes[$nameToken->value] = $valueNode->attributes['value'] ?? '';
                } else {
                    // PropertyAccess etc. stored as AST for compiler
                    $attributes[$nameToken->value] = $valueNode;
                }
            } else {
                throw new \RuntimeException('Expected attribute value');
            }

            if ($this->check(TokenType::Comma)) {
                $this->advance();
            }
        }

        $this->consume(TokenType::RightParen, "Expected ')' after attributes");

        return $attributes;
    }

    /**
     * Parse page chrome: pageheader(...) { chrome layout } or attribute-only preset.
     */
    private function parsePageChrome(string $keyword): AstNode
    {
        $attributes = $this->parseAttributes();
        $children = [];

        if ($this->match(TokenType::LeftBrace)) {
            while (!$this->check(TokenType::RightBrace) && !$this->isAtEnd()) {
                $child = $this->parseChromeStatement();
                if ($child !== null) {
                    $children[] = $child;
                }
            }
            $this->consume(TokenType::RightBrace, "Expected '}' after {$keyword} block");
        }

        return new AstNode('PageChrome', $children, [
            'kind' => $keyword,
            'attributes' => $attributes,
        ]);
    }

    /**
     * Statements allowed inside pageheader/pagefooter blocks.
     */
    private function parseChromeStatement(): ?AstNode
    {
        if ($this->isAtEnd()) {
            return null;
        }

        $token = $this->peek();
        if ($token->type === TokenType::Keyword) {
            $kw = $token->value;
            // Disallow body-only constructs inside chrome
            if (in_array($kw, ['page', 'table', 'tr', 'th', 'td', 'header', 'footer', 'pageheader', 'pagefooter'], true)) {
                throw new \RuntimeException("Element '{$kw}' is not allowed inside page chrome");
            }
            return $this->parseKeyword();
        }

        if ($token->type === TokenType::LeftBrace) {
            return $this->parseBlock();
        }

        if (
            $token->type === TokenType::String
            || $token->type === TokenType::Identifier
            || $token->type === TokenType::Number
        ) {
            return $this->parseExpression();
        }

        $this->advance();
        return null;
    }

    /**
     * Parse a var/prop declaration: var name = "default" or prop name = expr
     * Produces an AstNode with type 'VarDecl'.
     */
    private function parseVarDecl(string $keyword): AstNode
    {
        // var name = "value"
        // prop name = expression
        $nameToken = $this->consume(TokenType::Identifier, "Expected variable name after '{$keyword}'");
        $this->consume(TokenType::Equals, "Expected '=' after variable name");

        $defaultExpr = $this->parseExpression();

        return new AstNode('VarDecl', [], [
            'keyword' => $keyword,
            'name' => $nameToken->value,
            'default' => $defaultExpr,
        ]);
    }

    /**
     * Parse a partial include: partial "path/to/file" or partial name
     * Produces an AstNode with type 'Partial'.
     */
    private function parsePartial(): AstNode
    {
        // partial "path" or partial identifier
        if ($this->check(TokenType::String)) {
            $path = $this->advance();
            return new AstNode('Partial', [], ['path' => $path->value]);
        }

        if ($this->check(TokenType::Identifier) || $this->check(TokenType::Keyword)) {
            $name = $this->advance();
            return new AstNode('Partial', [], ['path' => $name->value]);
        }

        throw new \RuntimeException("Expected partial path after 'partial'");
    }

    private function parseBlock(): AstNode
    {
        $this->advance(); // {
        $children = [];

        while (!$this->check(TokenType::RightBrace) && !$this->isAtEnd()) {
            $child = $this->parseStatement();
            if ($child !== null) {
                $children[] = $child;
            }
        }

        $this->consume(TokenType::RightBrace, "Expected '}' after block");
        return new AstNode('Block', $children);
    }

    private function parseIf(): AstNode
    {
        $condition = $this->parseExpression();
        $thenBranch = $this->parseBlock();
        $elseBranch = null;

        if ($this->check(TokenType::Keyword) && $this->peek()->value === 'else') {
            $this->advance();
            $elseBranch = $this->parseBlock();
        }

        $children = [$thenBranch];
        if ($elseBranch !== null) {
            $children[] = $elseBranch;
        }

        return new AstNode('If', $children, ['condition' => $condition]);
    }

    private function parseForeach(): AstNode
    {
        // foreach collection as item { ... }
        $collection = $this->parseExpression();
        $as = $this->consume(TokenType::Keyword, "Expected 'as' after foreach collection");
        if ($as->value !== 'as') {
            throw new \RuntimeException("Expected 'as' after foreach collection");
        }
        // Item may be a keyword (e.g. "row") used as a loop variable name
        if ($this->check(TokenType::Identifier) || $this->check(TokenType::Keyword)) {
            $item = $this->advance();
        } else {
            throw new \RuntimeException("Expected item identifier after 'as'");
        }
        $body = $this->parseBlock();

        return new AstNode('Foreach', [$body], [
            'collection' => $collection,
            'item' => $item->value,
        ]);
    }

    private function parseExpression(): AstNode
    {
        if ($this->match(TokenType::String)) {
            return new AstNode('StringLiteral', [], ['value' => $this->previous()->value]);
        }

        if ($this->match(TokenType::Number)) {
            return new AstNode('NumberLiteral', [], ['value' => $this->previous()->value]);
        }

        // Identifiers AND keywords can be variable roots (row, text, header, etc.)
        if ($this->check(TokenType::Identifier) || $this->check(TokenType::Keyword)) {
            $parts = [$this->advance()->value];
            while ($this->match(TokenType::Dot)) {
                if ($this->check(TokenType::Identifier) || $this->check(TokenType::Keyword)) {
                    $parts[] = $this->advance()->value;
                } else {
                    throw new \RuntimeException("Expected property after '.'");
                }
            }

            if (count($parts) === 1) {
                return new AstNode('Identifier', [], ['value' => $parts[0]]);
            }

            return new AstNode('PropertyAccess', [], ['path' => $parts]);
        }

        throw new \RuntimeException("Unexpected token: {$this->peek()->value}");
    }

    private function match(TokenType $type): bool
    {
        if ($this->check($type)) {
            $this->advance();
            return true;
        }
        return false;
    }

    private function check(TokenType $type): bool
    {
        if ($this->isAtEnd()) {
            return false;
        }
        return $this->peek()->type === $type;
    }

    private function advance(): Token
    {
        if (!$this->isAtEnd()) {
            $this->position++;
        }
        return $this->previous();
    }

    private function consume(TokenType $type, string $message): Token
    {
        if ($this->check($type)) {
            return $this->advance();
        }
        throw new \RuntimeException($message . " (got {$this->peek()->type->name}: {$this->peek()->value})");
    }

    private function peek(): Token
    {
        return $this->tokens[$this->position];
    }

    private function previous(): Token
    {
        return $this->tokens[$this->position - 1];
    }

    private function isAtEnd(): bool
    {
        return $this->position >= count($this->tokens);
    }
}
