<?php

declare(strict_types=1);

namespace Folio\Pdf\Template;

final class FormatterFactory
{
    public static function create(): Formatter
    {
        return new Formatter();
    }

    public static function withIndent(int $size, string $character = ' '): Formatter
    {
        return new Formatter($size, $character);
    }

    public static function withTabs(int $tabSize = 4): Formatter
    {
        return new Formatter($tabSize, "\t");
    }

    public static function compact(): Formatter
    {
        return new Formatter(2, ' ');
    }

    public static function noFinalNewline(): Formatter
    {
        return new Formatter(4, ' ', false);
    }

    public static function fromConfig(array $config): Formatter
    {
        return new Formatter(
            $config['indentSize'] ?? 4,
            $config['indentCharacter'] ?? ' ',
            $config['insertFinalNewline'] ?? true
        );
    }
}
