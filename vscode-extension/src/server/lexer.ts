export enum TokenType {
    String = 'string',
    Number = 'number',
    Identifier = 'identifier',
    Keyword = 'keyword',
    Directive = 'directive',
    Equals = '=',
    EqualsEquals = '==',
    NotEquals = '!=',
    LessThan = '<',
    LessThanOrEqual = '<=',
    GreaterThan = '>',
    GreaterThanOrEqual = '>=',
    LeftBrace = '{',
    RightBrace = '}',
    LeftParen = '(',
    RightParen = ')',
    LeftBracket = '[',
    RightBracket = ']',
    Comma = ',',
    Dot = '.',
    At = '@',
    Bang = '!',
    Comment = 'comment',
    Unknown = 'unknown',
    EOF = 'eof',
}

export interface Token {
    type: TokenType;
    value: string;
    start: number;
    end: number;
    line: number;
    character: number;
}

const keywords = new Set<string>([
    'page', 'column', 'row', 'text', 'heading',
    'table', 'tr', 'th', 'td', 'header', 'footer',
    'if', 'else', 'elseif', 'foreach', 'as', 'empty',
    'var', 'prop', 'partial', 'pageheader', 'pagefooter',
    'monogram', 'badge', 'spacer', 'rule', 'box', 'pagenum', 'img',
    'and', 'or', 'not',
]);

export interface TokenizeResult {
    tokens: Token[];
    errors: LexerError[];
}

export interface LexerError {
    message: string;
    line: number;
    character: number;
    length: number;
}

export function tokenize(input: string): TokenizeResult {
    const tokens: Token[] = [];
    const errors: LexerError[] = [];
    let position = 0;
    let line = 0;
    let character = 0;
    const length = input.length;

    function currentChar(): string {
        return position < length ? input[position] : '';
    }

    function peekChar(offset = 1): string {
        const next = position + offset;
        return next < length ? input[next] : '';
    }

    function advance(): void {
        if (position < length) {
            if (input[position] === '\n') {
                line++;
                character = 0;
            } else {
                character++;
            }
            position++;
        }
    }

    function startToken(): { start: number; line: number; character: number } {
        return { start: position, line, character };
    }

    function makeToken(
        type: TokenType,
        value: string,
        meta: { start: number; line: number; character: number },
    ): Token {
        return {
            type,
            value,
            start: meta.start,
            end: position,
            line: meta.line,
            character: meta.character,
        };
    }

    function lexComment(): Token {
        const meta = startToken();
        advance(); // '/'
        advance(); // '/'
        while (position < length && currentChar() !== '\n') {
            advance();
        }
        return makeToken(TokenType.Comment, input.slice(meta.start, position), meta);
    }

    function lexIdentifier(): Token {
        const meta = startToken();
        while (position < length) {
            const ch = currentChar();
            if (!/[A-Za-z0-9_]/.test(ch)) {
                break;
            }
            advance();
        }
        const value = input.slice(meta.start, position);
        const type = keywords.has(value) ? TokenType.Keyword : TokenType.Identifier;
        return makeToken(type, value, meta);
    }

    function lexString(): Token {
        const meta = startToken();
        const quote = currentChar();
        advance();
        while (position < length) {
            const ch = currentChar();
            if (ch === '\\') {
                advance();
                if (position >= length) {
                    break;
                }
                advance();
                continue;
            }
            if (ch === quote) {
                advance();
                return makeToken(TokenType.String, input.slice(meta.start, position), meta);
            }
            advance();
        }
        errors.push({
            message: 'Unterminated string literal',
            line: meta.line,
            character: meta.character,
            length: position - meta.start,
        });
        return makeToken(TokenType.String, input.slice(meta.start, position), meta);
    }

    function lexNumber(): Token {
        const meta = startToken();
        while (position < length && /\d/.test(currentChar())) {
            advance();
        }
        if (position < length && currentChar() === '.') {
            advance();
            while (position < length && /\d/.test(currentChar())) {
                advance();
            }
        }
        return makeToken(TokenType.Number, input.slice(meta.start, position), meta);
    }

    function lexAtSymbol(): Token {
        const meta = startToken();
        advance();
        if (position < length && /[A-Za-z]/.test(currentChar())) {
            while (position < length && /[A-Za-z0-9_]/.test(currentChar())) {
                advance();
            }
            return makeToken(TokenType.Directive, input.slice(meta.start, position), meta);
        }
        return makeToken(TokenType.At, '@', meta);
    }

    function lexEquals(): Token {
        const meta = startToken();
        advance();
        if (currentChar() === '=') {
            advance();
            return makeToken(TokenType.EqualsEquals, '==', meta);
        }
        return makeToken(TokenType.Equals, '=', meta);
    }

    function lexBang(): Token {
        const meta = startToken();
        advance();
        if (currentChar() === '=') {
            advance();
            return makeToken(TokenType.NotEquals, '!=', meta);
        }
        return makeToken(TokenType.Bang, '!', meta);
    }

    function lexLessThan(): Token {
        const meta = startToken();
        advance();
        if (currentChar() === '=') {
            advance();
            return makeToken(TokenType.LessThanOrEqual, '<=', meta);
        }
        return makeToken(TokenType.LessThan, '<', meta);
    }

    function lexGreaterThan(): Token {
        const meta = startToken();
        advance();
        if (currentChar() === '=') {
            advance();
            return makeToken(TokenType.GreaterThanOrEqual, '>=', meta);
        }
        return makeToken(TokenType.GreaterThan, '>', meta);
    }

    while (position < length) {
        const ch = currentChar();
        if (/\s/.test(ch)) {
            advance();
            continue;
        }
        if (ch === '/' && peekChar() === '/') {
            tokens.push(lexComment());
            continue;
        }
        if (/[A-Za-z_]/.test(ch)) {
            tokens.push(lexIdentifier());
            continue;
        }
        if (ch === '"' || ch === "'") {
            tokens.push(lexString());
            continue;
        }
        if (/\d/.test(ch)) {
            tokens.push(lexNumber());
            continue;
        }

        switch (ch) {
            case '{':
                tokens.push(makeToken(TokenType.LeftBrace, '{', startToken()));
                advance();
                break;
            case '}':
                tokens.push(makeToken(TokenType.RightBrace, '}', startToken()));
                advance();
                break;
            case '(':
                tokens.push(makeToken(TokenType.LeftParen, '(', startToken()));
                advance();
                break;
            case ')':
                tokens.push(makeToken(TokenType.RightParen, ')', startToken()));
                advance();
                break;
            case '[':
                tokens.push(makeToken(TokenType.LeftBracket, '[', startToken()));
                advance();
                break;
            case ']':
                tokens.push(makeToken(TokenType.RightBracket, ']', startToken()));
                advance();
                break;
            case ',':
                tokens.push(makeToken(TokenType.Comma, ',', startToken()));
                advance();
                break;
            case '.':
                tokens.push(makeToken(TokenType.Dot, '.', startToken()));
                advance();
                break;
            case '@':
                tokens.push(lexAtSymbol());
                break;
            case '=':
                tokens.push(lexEquals());
                break;
            case '!':
                tokens.push(lexBang());
                break;
            case '<':
                tokens.push(lexLessThan());
                break;
            case '>':
                tokens.push(lexGreaterThan());
                break;
            default:
                errors.push({
                    message: `Unexpected character '${ch}'`,
                    line,
                    character,
                    length: 1,
                });
                tokens.push(makeToken(TokenType.Unknown, ch, startToken()));
                advance();
        }
    }

    return { tokens, errors };
}

export function getTokenAt(tokens: Token[], line: number, character: number): Token | null {
    for (const token of tokens) {
        if (
            token.line === line &&
            token.character <= character &&
            token.character + token.value.length >= character
        ) {
            return token;
        }
    }
    return null;
}

export function getWordAt(text: string, line: number, character: number): string {
    const lines = text.split('\n');
    const lineText = lines[line] ?? '';
    if (lineText === '') {
        return '';
    }
    const len = lineText.length;
    let i = Math.min(character, len);
    if (i > 0 && i < len && !/[A-Za-z0-9_@]/.test(lineText[i])) {
        i--;
    }
    let start = i;
    while (start > 0 && /[A-Za-z0-9_@]/.test(lineText[start - 1])) {
        start--;
    }
    let end = i;
    while (end < len && /[A-Za-z0-9_@]/.test(lineText[end])) {
        end++;
    }
    return lineText.slice(start, end);
}
