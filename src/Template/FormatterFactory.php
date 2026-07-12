<?php

declare(strict_types=1);

namespace Folio\Pdf\Template;

/**
 * Factory for creating formatters with different configurations.
 */
final class FormatterFactory
{
    /**
     * Create a formatter with default settings.
     */
    public static function create(): Formatter
    {
        return new Formatter();
    }

    /**
     * Create a formatter with custom indentation.
     */
    public static function withIndent(int $size, string $character = ' '): Formatter
    {
        return new Formatter($size, $character);
    }

    /**
     * Create a formatter for tabs.
     */
    public static function withTabs(int $tabSize = 4): Formatter
    {
        return new Formatter($tabSize, "\t");
    }

    /**
     * Create a compact formatter (2 spaces).
     */
    public static function compact(): Formatter
    {
        return new Formatter(2, ' ');
    }

    /**
     * Create a formatter that doesn't add final newlines.
     */
    public static function noFinalNewline(): Formatter
    {
        return new Formatter(4, ' ', false);
    }

    /**
     * Create a formatter from configuration array.
     */
    public static function fromConfig(array $config): Formatter
    {
        return new Formatter(
            $config['indentSize'] ?? 4,
            $config['indentCharacter'] ?? ' ',
            $config['insertFinalNewline'] ?? true
        );
    }
}
