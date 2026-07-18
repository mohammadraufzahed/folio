<?php

declare(strict_types=1);

namespace Folio\Pdf\StyleEngine;

use Folio\Pdf\Ports\StyleParserPort;

final class FolioStyleParser implements StyleParserPort
{
    /**
     * @var array<string, string>
     */
    private const CONDITION_MAP = [
        'first-page' => 'firstPage',
        'firstPage' => 'firstPage',
        'last-page' => 'lastPage',
        'lastPage' => 'lastPage',
        'odd' => 'odd',
        'even' => 'even',
        'landscape' => 'landscape',
        'portrait' => 'portrait',
        'header' => 'header',
        'footer' => 'footer',
    ];

    public function parse(string $source): StyleSheet
    {
        $source = $this->stripComments($source);

        $rules = [];
        $position = 0;
        $length = strlen($source);

        while ($position < $length) {
            $block = $this->nextBlock($source, $position);

            if ($block === null) {
                break;
            }

            foreach ($block['selectors'] as $selector) {
                $rules[] = $this->buildRule($selector, $block['properties']);
            }
        }

        return new StyleSheet(...$rules);
    }

    private function stripComments(string $source): string
    {
        $source = preg_replace('/\/\*[\s\S]*?\*\//', '', $source) ?? $source;

        return preg_replace('/\/\/[^\n]*/', '', $source) ?? $source;
    }

    /**
     * @return ?array{selectors: list<string>, properties: array<string, mixed>}
     */
    private function nextBlock(string $source, int &$position): ?array
    {
        $length = strlen($source);

        while ($position < $length && ctype_space($source[$position])) {
            $position++;
        }

        if ($position >= $length) {
            return null;
        }

        $braceStart = strpos($source, '{', $position);

        if ($braceStart === false) {
            $position = $length;

            return null;
        }

        $selectorBlock = substr($source, $position, $braceStart - $position);
        $selectors = $this->parseSelectors($selectorBlock);

        $braceEnd = $this->findMatchingBrace($source, $braceStart);

        if ($braceEnd === null) {
            throw new \RuntimeException('Unclosed style block in @style');
        }

        $properties = $this->parseProperties(
            trim(substr($source, $braceStart + 1, $braceEnd - $braceStart - 1))
        );

        $position = $braceEnd + 1;

        if ($selectors === []) {
            return null;
        }

        return ['selectors' => $selectors, 'properties' => $properties];
    }

    /**
     * @return list<string>
     */
    private function parseSelectors(string $selectorBlock): array
    {
        $selectors = [];

        foreach (explode(',', $selectorBlock) as $selector) {
            $selector = trim($selector);

            if ($selector !== '') {
                $selectors[] = $selector;
            }
        }

        return $selectors;
    }

    private function findMatchingBrace(string $source, int $openPosition): ?int
    {
        $length = strlen($source);
        $depth = 1;

        for ($i = $openPosition + 1; $i < $length; $i++) {
            if ($source[$i] === '{') {
                $depth++;
            } elseif ($source[$i] === '}') {
                $depth--;

                if ($depth === 0) {
                    return $i;
                }
            }
        }

        return null;
    }

    /**
     * @return array<string, mixed>
     */
    private function parseProperties(string $blockContent): array
    {
        $properties = [];

        foreach ($this->tokenizeProperties($blockContent) as $declaration) {
            $declaration = trim($declaration);

            if ($declaration === '') {
                continue;
            }

            $colon = strpos($declaration, ':');

            if ($colon === false) {
                continue;
            }

            $key = trim(substr($declaration, 0, $colon));
            $value = trim(substr($declaration, $colon + 1));

            if ($key === '') {
                continue;
            }

            $properties[$key] = $value;
        }

        return $properties;
    }

    /**
     * @return list<string>
     */
    private function tokenizeProperties(string $blockContent): array
    {
        $parts = [];
        $current = '';
        $depth = 0;
        $length = strlen($blockContent);

        for ($i = 0; $i < $length; $i++) {
            $char = $blockContent[$i];

            if ($char === '{') {
                $depth++;
            } elseif ($char === '}') {
                $depth--;
            } elseif ($char === ';' && $depth === 0) {
                $parts[] = $current;
                $current = '';
                continue;
            }

            $current .= $char;
        }

        if ($current !== '') {
            $parts[] = $current;
        }

        return $parts;
    }

    /**
     * @param array<string, mixed> $properties
     */
    private function buildRule(string $selector, array $properties): StyleRule
    {
        $conditions = [];
        $cleanSelector = $selector;

        if (str_starts_with($selector, ':')) {
            $condition = self::CONDITION_MAP[ltrim($selector, ':')] ?? null;

            if ($condition !== null) {
                $conditions[] = ':' . $condition;
            }

            return new StyleRule($selector, $properties, $conditions);
        }

        if (str_starts_with($selector, '[') && str_ends_with($selector, ']')) {
            $condition = trim($selector, '[]');

            if ($condition !== '') {
                $conditions[] = '[' . $condition . ']';
            }

            return new StyleRule($selector, $properties, $conditions);
        }

        return new StyleRule($cleanSelector, $properties, $conditions);
    }
}
