<?php

declare(strict_types=1);

namespace Folio\Pdf\Template;

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

    public function format(string $template): string
    {
        $lines = explode("\n", $template);
        $formatted = [];
        $indentLevel = 0;
        $inBlock = false;

        foreach ($lines as $line) {
            $trimmed = trim($line);

            if ($trimmed === '') {
                if (!empty($formatted) && end($formatted) !== '') {
                    $formatted[] = '';
                }
                continue;
            }

            if (str_starts_with($trimmed, '//')) {
                $formatted[] = $this->indent($indentLevel) . $trimmed;
                continue;
            }

            if (str_starts_with($trimmed, '}')) {
                $indentLevel = max(0, $indentLevel - 1);
                $inBlock = false;
            }

            $formattedLine = $this->formatLine($trimmed, $indentLevel);
            $formatted[] = $formattedLine;

            if (str_ends_with($trimmed, '{')) {
                $indentLevel++;
                $inBlock = true;
            } elseif (preg_match('/^(if|foreach|else|elseif)\b/', $trimmed)) {
                $indentLevel++;
                $inBlock = true;
            }

            if (str_contains($trimmed, '}')) {
                $indentLevel = max(0, $indentLevel - 1);
                $inBlock = false;
            }
        }

        while (!empty($formatted) && end($formatted) === '') {
            array_pop($formatted);
        }
        $result = implode("\n", $formatted);
        if ($this->insertFinalNewline) {
            $result .= "\n";
        }

        return $result;
    }

    private function formatLine(string $line, int $indentLevel): string
    {
        $indent = $this->indent($indentLevel);

        if (str_starts_with($line, '@')) {
            return $indent . $line;
        }

        if (preg_match('/^(page|column|row|text|heading)\b/', $line)) {
            return $this->formatElement($line, $indent);
        }

        if (preg_match('/^(if|foreach|else|elseif)\b/', $line)) {
            return $this->formatControlStructure($line, $indent);
        }

        return $indent . $line;
    }

    private function formatElement(string $line, string $indent): string
    {
        if (preg_match('/^(\w+)(?:\s*\(([^)]*)\))?\s*(\{)?(.*)$/', $line, $matches)) {
            $element = $matches[1];
            $attributes = $matches[2];
            $hasBrace = $matches[3] === '{';
            $content = trim($matches[4]);

            $result = $indent . $element;

            if ($attributes !== '') {
                $formattedAttributes = $this->formatAttributes($attributes);
                $result .= '(' . $formattedAttributes . ')';
            }

            if ($hasBrace) {
                $result .= ' {';
            }

            if ($content !== '') {
                $result .= ' ' . $content;
            }

            return $result;
        }

        return $indent . $line;
    }

    private function formatControlStructure(string $line, string $indent): string
    {
        if (preg_match('/^(if|foreach|else|elseif)\s+(.*)$/', $line, $matches)) {
            $keyword = $matches[1];
            $condition = trim($matches[2]);

            $result = $indent . $keyword . ' ';

            if (str_ends_with($condition, '{')) {
                $result .= trim(substr($condition, 0, -1)) . ' {';
            } else {
                $result .= $condition;
            }

            return $result;
        }

        return $indent . $line;
    }

    private function formatAttributes(string $attributes): string
    {
        $parts = explode(',', $attributes);
        $formatted = [];

        foreach ($parts as $part) {
            $formatted[] = trim($part);
        }

        return implode(', ', $formatted);
    }

    private function indent(int $level): string
    {
        return str_repeat($this->indentCharacter, $level * $this->indentSize);
    }

    public function formatFile(string $path): string
    {
        $content = file_get_contents($path);
        $formatted = $this->format($content);
        file_put_contents($path, $formatted);
        return $formatted;
    }

    public function needsFormatting(string $path): bool
    {
        $content = file_get_contents($path);
        $formatted = $this->format($content);
        return $content !== $formatted;
    }

    public function formatIfChanged(string $path): bool
    {
        if ($this->needsFormatting($path)) {
            $this->formatFile($path);
            return true;
        }
        return false;
    }
}
