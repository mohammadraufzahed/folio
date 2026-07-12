<?php

declare(strict_types=1);

namespace Folio\Pdf\Template;

/**
 * Token type enumeration.
 */
enum TokenType: string
{
    // Literals
    case String = 'string';
    case Number = 'number';
    case Identifier = 'identifier';

    // Keywords
    case Keyword = 'keyword';

    // Directives
    case Directive = 'directive';

    // Operators
    case Equals = '=';
    case EqualsEquals = '==';
    case NotEquals = '!=';
    case LessThan = '<';
    case LessThanOrEqual = '<=';
    case GreaterThan = '>';
    case GreaterThanOrEqual = '>=';

    // Punctuation
    case LeftBrace = '{';
    case RightBrace = '}';
    case LeftParen = '(';
    case RightParen = ')';
    case LeftBracket = '[';
    case RightBracket = ']';
    case Comma = ',';
    case Dot = '.';
    case At = '@';
    case Bang = '!';

    // Other
    case Comment = 'comment';
    case Unknown = 'unknown';
    case EOF = 'eof';
}
