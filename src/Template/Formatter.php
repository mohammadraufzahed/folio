<?php

declare(strict_types=1);

namespace Folio\Pdf\Template;

/**
 * Formatter for Folio PDF template language.
 */
final class Formatter
{
    private readonly int $indentSize;
    private readonly string $indentCharacter;
    private readonly bool $insertFinalNewline;

    public function __construct(
        int $indentSize = 4,
        string $indentCharacter = ' ',
        bool $insertFinalNewline = true
    ) {
        $this->indentSize = $indentSize;
        $this->indentCharacter = $indentCharacter;
        $this->insertFinalNewline = $insertFinalNewline;
    }

    /**
     * Format a template string.
     */
    public function format(string $template): string
    {
        $lines = explode("\n", $template);
        $formatted = [];
        $indentLevel = 0;
        $inBlock = false;

        foreach ($lines as $line) {
            $trimmed = trim($line);

            // Skip empty lines (preserve single empty lines between blocks)
            if ($trimmed === '') {
                if (!empty($formatted) && end($formatted) !== '') {
                    $formatted[] = '';
                }
                continue;
            }

            // Skip comments
            if (str_starts_with($trimmed, '//')) {
                $formatted[] = $this->indent($indentLevel) . $trimmed;
                continue;
            }

            // Handle closing braces
            if (str_starts_with($trimmed, '}')) {
                $indentLevel = max(0, $indentLevel - 1);
                $inBlock = false;
            }

            // Format the line
            $formattedLine = $this->formatLine($trimmed, $indentLevel);
            $formatted[] = $formattedLine;

            // Handle opening braces and control structures
            if (str_ends_with($trimmed, '{')) {
                $indentLevel++;
                $inBlock = true;
            } elseif (preg_match('/^(if|foreach|else|elseif)\b/', $trimmed)) {
                $indentLevel++;
                $inBlock = true;
            }

            // Handle closing braces on same line
            if (str_contains($trimmed, '}')) {
                $indentLevel = max(0, $indentLevel - 1);
                $inBlock = false;
            }
        }

        // Remove trailing empty lines
        while (!empty($formatted) && end($formatted) === '') {
            array_pop($formatted);
        }

        // Join and add final newline if requested
        $result = implode("\n", $formatted);
        if ($this->insertFinalNewline) {
            $result .= "\n";
        }

        return $result;
    }

    /**
     * Format a single line.
     */
    private function formatLine(string $line, int $indentLevel): string
    {
        $indent = $this->indent($indentLevel);

        // Format directives
        if (str_starts_with($line, '@')) {
            return $indent . $line;
        }

        // Format element declarations
        if (preg_match('/^(page|column|row|text|heading)\b/', $line)) {
            return $this->formatElement($line, $indent);
        }

        // Format control structures
        if (preg_match('/^(if|foreach|else|elseif)\b/', $line)) {
            return $this->formatControlStructure($line, $indent);
        }

        // Format content
        return $indent . $line;
    }

    /**
     * Format an element declaration.
     */
    private function formatElement(string $line, string $indent): string
    {
        // Extract element name and content
        if (preg_match('/^(\w+)(?:\s*\(([^)]*)\))?\s*(\{)?(.*)$/', $line, $matches)) {
            $element = $matches[1];
            $attributes = $matches[2] ?? '';
            $hasBrace = $matches[3] === '{';
            $content = trim($matches[4] ?? '');

            $result = $indent . $element;

            // Add attributes if present
            if ($attributes !== '') {
                $formattedAttributes = $this->formatAttributes($attributes);
                $result .= '(' . $formattedAttributes . ')';
            }

            // Add brace if present
            if ($hasBrace) {
                $result .= ' {';
            }

            // Add content if present
            if ($content !== '') {
                $result .= ' ' . $content;
            }

            return $result;
        }

        return $indent . $line;
    }

    /**
     * Format control structure.
     */
    private function formatControlStructure(string $line, string $indent): string
    {
        if (preg_match('/^(if|foreach|else|elseif)\s+(.*)$/', $line, $matches)) {
            $keyword = $matches[1];
            $condition = trim($matches[2]);

            $result = $indent . $keyword . ' ';

            // Format condition
            if (str_ends_with($condition, '{')) {
                $result .= trim(substr($condition, 0, -1)) . ' {';
            } else {
                $result .= $condition;
            }

            return $result;
        }

        return $indent . $line;
    }

    /**
     * Format attributes.
     */
    private function formatAttributes(string $attributes): string
    {
        $parts = explode(',', $attributes);
        $formatted = [];

        foreach ($parts as $part) {
            $formatted[] = trim($part);
        }

        return implode(', ', $formatted);
    }

    /**
     * Generate indentation string.
     */
    private function indent(int $level): string
    {
        return str_repeat($this->indentCharacter, $level * $this->indentSize);
    }

    /**
     * Format a file.
     */
    public function formatFile(string $path): string
    {
        $content = file_get_contents($path);
        $formatted = $this->format($content);
        file_put_contents($path, $formatted);
        return $formatted;
    }

    /**
     * Check if a file needs formatting.
     */
    public function needsFormatting(string $path): bool
    {
        $content = file_get_contents($path);
        $formatted = $this->format($content);
        return $content !== $formatted;
    }

    /**
     * Format and save if needed.
     */
    public function formatIfChanged(string $path): bool
    {
        if ($this->needsFormatting($path)) {
            $this->formatFile($path);
            return true;
        }
        return false;
    }
}
