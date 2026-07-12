<?php

declare(strict_types=1);

namespace Folio\Pdf\Template;

use Folio\Pdf\Template\Error\TemplateError;

/**
 * Parser for the Folio template language.
 *
 * Recursive-descent parser with a Pratt-style expression parser
 * supporting comparisons, boolean operators, and grouping.
 */
final class Parser
{
    private int $position = 0;
    /** @var array<int, Token> */
    private array $tokens;

    /**
     * @param array<int, Token> $tokens
     */
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
            default => $this->error("Unexpected token {$token->type->name} '{$token->value}'", $token),
        };
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
            default => $this->error("Unknown keyword '{$keyword}'", $token),
        };
    }

    private function parseElement(string $type): AstNode
    {
        $children = [];
        $attributes = $this->parseAttributes();

        if (
            $this->check(TokenType::String)
            || $this->check(TokenType::Identifier)
            || $this->check(TokenType::Number)
            || $this->check(TokenType::Keyword)
        ) {
            $children[] = $this->parseExpression();
        }

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
            $this->consume(TokenType::Equals, "Expected '=' after attribute name '{$nameToken->value}'");

            if (
                $this->check(TokenType::String) || $this->check(TokenType::Number)
                || $this->check(TokenType::Identifier) || $this->check(TokenType::Keyword)
                || $this->check(TokenType::LeftParen)
            ) {
                $valueNode = $this->parseExpression();
                if ($valueNode->type === 'StringLiteral' || $valueNode->type === 'NumberLiteral') {
                    $attributes[$nameToken->value] = $valueNode->attributes['value'] ?? '';
                } elseif ($valueNode->type === 'Identifier') {
                    $attributes[$nameToken->value] = $valueNode->attributes['value'] ?? '';
                } else {
                    $attributes[$nameToken->value] = $valueNode;
                }
            } else {
                $this->error('Expected attribute value', $this->peek());
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
            if (in_array($kw, ['page', 'table', 'tr', 'th', 'td', 'header', 'footer', 'pageheader', 'pagefooter'], true)) {
                $this->error("Element '{$kw}' is not allowed inside page chrome", $token);
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

        $this->error("Unexpected token in page chrome: {$token->type->name} '{$token->value}'", $token);
    }

    /**
     * Parse a var/prop declaration: var name = "default" or prop name = expr
     */
    private function parseVarDecl(string $keyword): AstNode
    {
        $nameToken = $this->consume(TokenType::Identifier, "Expected variable name after '{$keyword}'");
        $this->consume(TokenType::Equals, "Expected '=' after variable name '{$nameToken->value}'");

        $defaultExpr = $this->parseExpression();

        return new AstNode('VarDecl', [], [
            'keyword' => $keyword,
            'name' => $nameToken->value,
            'default' => $defaultExpr,
        ]);
    }

    /**
     * Parse a partial include: partial "path/to/file" or partial name
     */
    private function parsePartial(): AstNode
    {
        if ($this->check(TokenType::String)) {
            $path = $this->advance();
            return new AstNode('Partial', [], ['path' => $path->value]);
        }

        if ($this->check(TokenType::Identifier) || $this->check(TokenType::Keyword)) {
            $name = $this->advance();
            return new AstNode('Partial', [], ['path' => $name->value]);
        }

        $this->error("Expected path after 'partial'", $this->peek());
    }

    private function parseBlock(): AstNode
    {
        $this->advance();
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

    /**
     * Parse if/elseif/else chains.
     *
     * AST: If node with children = [thenBlock, elseIf1, elseIf2, ..., elseBlock?]
     * Each elseIf is an If node stored as a child.
     */
    private function parseIf(): AstNode
    {
        $condition = $this->parseExpression();
        $thenBranch = $this->parseBlock();

        $children = [$thenBranch];
        $attributes = ['condition' => $condition];

        if ($this->check(TokenType::Keyword) && $this->peek()->value === 'else') {
            $this->advance();

            if ($this->check(TokenType::Keyword) && $this->peek()->value === 'if') {
                $this->advance();
                $children[] = $this->parseIf();
            } elseif ($this->check(TokenType::Keyword) && $this->peek()->value === 'elseif') {
                $this->advance();
                $children[] = $this->parseIfContinued();
            } else {
                $children[] = $this->parseBlock();
            }
        } elseif ($this->check(TokenType::Keyword) && $this->peek()->value === 'elseif') {
            $this->advance();
            $children[] = $this->parseIfContinued();
        }

        return new AstNode('If', $children, $attributes);
    }

    /**
     * Parse the rest of an elseif chain (condition + block + more elseif/else).
     */
    private function parseIfContinued(): AstNode
    {
        $condition = $this->parseExpression();
        $thenBranch = $this->parseBlock();

        $children = [$thenBranch];
        $attributes = ['condition' => $condition];

        if ($this->check(TokenType::Keyword) && $this->peek()->value === 'else') {
            $this->advance();
            if ($this->check(TokenType::Keyword) && $this->peek()->value === 'if') {
                $this->advance();
                $children[] = $this->parseIf();
            } elseif ($this->check(TokenType::Keyword) && $this->peek()->value === 'elseif') {
                $this->advance();
                $children[] = $this->parseIfContinued();
            } else {
                $children[] = $this->parseBlock();
            }
        } elseif ($this->check(TokenType::Keyword) && $this->peek()->value === 'elseif') {
            $this->advance();
            $children[] = $this->parseIfContinued();
        }

        return new AstNode('If', $children, $attributes);
    }

    /**
     * Parse foreach with optional index and empty block.
     *
     * Syntax:
     *   foreach collection as item { ... }
     *   foreach collection as index, item { ... }
     *   foreach collection as item { ... } empty { ... }
     */
    private function parseForeach(): AstNode
    {
        $collection = $this->parseExpression();
        $as = $this->consume(TokenType::Keyword, "Expected 'as' after foreach collection");
        if ($as->value !== 'as') {
            $this->error("Expected 'as' after foreach collection, got '{$as->value}'", $as);
        }

        $index = null;
        if ($this->check(TokenType::Identifier) || $this->check(TokenType::Keyword)) {
            $first = $this->advance();
            if ($this->check(TokenType::Comma)) {
                $this->advance();
                if ($this->check(TokenType::Identifier) || $this->check(TokenType::Keyword)) {
                    $index = $first->value;
                    $item = $this->advance();
                } else {
                    $this->error("Expected item identifier after comma in foreach", $this->peek());
                }
            } else {
                $item = $first;
            }
        } else {
            $this->error("Expected item identifier after 'as'", $this->peek());
        }

        $body = $this->parseBlock();

        $emptyBody = null;
        if ($this->check(TokenType::Keyword) && $this->peek()->value === 'empty') {
            $this->advance();
            $emptyBody = $this->parseBlock();
        }

        $children = [$body];
        if ($emptyBody !== null) {
            $children[] = $emptyBody;
        }

        return new AstNode('Foreach', $children, [
            'collection' => $collection,
            'item' => $item->value,
            'index' => $index,
            'hasEmpty' => $emptyBody !== null,
        ]);
    }

    private function parseExpression(): AstNode
    {
        return $this->parseOr();
    }

    private function parseOr(): AstNode
    {
        $left = $this->parseAnd();

        while ($this->check(TokenType::Keyword) && $this->peek()->value === 'or') {
            $op = $this->advance();
            $right = $this->parseAnd();
            $left = new AstNode('BinaryOp', [$left, $right], ['op' => 'or']);
        }

        return $left;
    }

    private function parseAnd(): AstNode
    {
        $left = $this->parseNot();

        while ($this->check(TokenType::Keyword) && $this->peek()->value === 'and') {
            $op = $this->advance();
            $right = $this->parseNot();
            $left = new AstNode('BinaryOp', [$left, $right], ['op' => 'and']);
        }

        return $left;
    }

    private function parseNot(): AstNode
    {
        if ($this->check(TokenType::Keyword) && $this->peek()->value === 'not') {
            $this->advance();
            $operand = $this->parseNot();
            return new AstNode('UnaryOp', [$operand], ['op' => 'not']);
        }

        if ($this->check(TokenType::Bang)) {
            $this->advance();
            $operand = $this->parseNot();
            return new AstNode('UnaryOp', [$operand], ['op' => 'not']);
        }

        return $this->parseComparison();
    }

    private function parseComparison(): AstNode
    {
        $left = $this->parsePrimary();

        $comparisonTypes = [
            TokenType::EqualsEquals,
            TokenType::NotEquals,
            TokenType::LessThan,
            TokenType::LessThanOrEqual,
            TokenType::GreaterThan,
            TokenType::GreaterThanOrEqual,
        ];

        while ($this->check($comparisonTypes)) {
            $opToken = $this->advance();
            $op = match ($opToken->type) {
                TokenType::EqualsEquals => '==',
                TokenType::NotEquals => '!=',
                TokenType::LessThan => '<',
                TokenType::LessThanOrEqual => '<=',
                TokenType::GreaterThan => '>',
                TokenType::GreaterThanOrEqual => '>=',
                default => '==',
            };
            $right = $this->parsePrimary();
            $left = new AstNode('BinaryOp', [$left, $right], ['op' => $op]);
        }

        return $left;
    }

    private function parsePrimary(): AstNode
    {
        if ($this->match(TokenType::LeftParen)) {
            $expr = $this->parseExpression();
            $this->consume(TokenType::RightParen, "Expected ')' after expression");
            return $expr;
        }

        if ($this->match(TokenType::String)) {
            return new AstNode('StringLiteral', [], ['value' => $this->previous()->value]);
        }

        if ($this->match(TokenType::Number)) {
            return new AstNode('NumberLiteral', [], ['value' => $this->previous()->value]);
        }

        if ($this->check(TokenType::Identifier) || $this->check(TokenType::Keyword)) {
            $parts = [$this->advance()->value];
            while ($this->match(TokenType::Dot)) {
                if ($this->check(TokenType::Identifier) || $this->check(TokenType::Keyword)) {
                    $parts[] = $this->advance()->value;
                } else {
                    $this->error("Expected property after '.'", $this->peek());
                }
            }

            if (count($parts) === 1) {
                return new AstNode('Identifier', [], ['value' => $parts[0]]);
            }

            return new AstNode('PropertyAccess', [], ['path' => $parts]);
        }

        $token = $this->peek();
        $this->error("Unexpected token in expression: {$token->type->name} '{$token->value}'", $token);
    }

    private function match(TokenType $type): bool
    {
        if ($this->check($type)) {
            $this->advance();
            return true;
        }
        return false;
    }

    /**
     * @param TokenType|list<TokenType> $type
     */
    private function check(TokenType|array $type): bool
    {
        if ($this->isAtEnd()) {
            return false;
        }
        $t = $this->peek()->type;
        if (is_array($type)) {
            return in_array($t, $type, true);
        }
        return $t === $type;
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
        $token = $this->peek();
        $this->error(
            $message . " (got {$token->type->name}: '{$token->value}')",
            $token,
        );
    }

    /**
     * Throw a TemplateError with the token's source location.
     *
     * @return never
     */
    private function error(string $message, Token $token): never
    {
        throw new TemplateError(
            $message,
            $token->line,
            $token->column,
            $token->length,
        );
    }

    private function peek(): Token
    {
        if ($this->position >= count($this->tokens)) {
            $last = $this->tokens[count($this->tokens) - 1] ?? null;
            $line = $last?->line ?? 1;
            $column = $last?->column + $last?->length ?? 1;
            return new Token(TokenType::EOF, '', PHP_INT_MAX, $line, $column, 0);
        }
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
