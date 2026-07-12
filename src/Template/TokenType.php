<?php

declare(strict_types=1);

namespace Folio\Pdf\Template;

/**
 * Token type enumeration.
 */
enum TokenType: string
{
    case String = 'string';
    case Number = 'number';
    case Identifier = 'identifier';

    case Keyword = 'keyword';

    case Directive = 'directive';

    case Equals = '=';
    case EqualsEquals = '==';
    case NotEquals = '!=';
    case LessThan = '<';
    case LessThanOrEqual = '<=';
    case GreaterThan = '>';
    case GreaterThanOrEqual = '>=';

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

    case Comment = 'comment';
    case Unknown = 'unknown';
    case EOF = 'eof';
}
