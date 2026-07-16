<?php

declare(strict_types=1);

namespace Folio\Pdf\StyleEngine;

final readonly class StyleRule
{
    /**
     * @param array<string, mixed> $properties
     * @param array<int, string> $conditions
     */
    public function __construct(
        public string $selector,
        public array $properties = [],
        public array $conditions = [],
    ) {
    }

    /**
     * @param array<int, string> $classList
     */
    public function matches(string $type, array $classList, StyleContext $context): bool
    {
        $selector = trim($this->selector);

        if ($selector === '') {
            return false;
        }

        if (str_contains($type, '\\')) {
            $type = substr($type, (int) strrpos($type, '\\') + 1);
        }

        $type = strtolower($type);

        if (!empty($this->conditions) && !$this->conditionsMet($context)) {
            return false;
        }

        if (str_starts_with($selector, '.')) {
            $target = substr($selector, 1);

            return in_array($target, $classList, true);
        }

        if (str_starts_with($selector, ':')) {
            return $this->conditionName($selector) !== null && $this->conditionsMet($context);
        }

        if (str_starts_with($selector, '[') && str_ends_with($selector, ']')) {
            $attribute = trim($selector, '[]');

            return ($context->state[$attribute] ?? false) === true;
        }

        return $type === $selector;
    }

    private function conditionsMet(StyleContext $context): bool
    {
        foreach ($this->conditions as $condition) {
            $key = $this->conditionName($condition);

            if ($key === null) {
                return false;
            }

            if (!($context->state[$key] ?? false)) {
                return false;
            }
        }

        return true;
    }

    private function conditionName(string $condition): ?string
    {
        $name = ltrim($condition, ':');

        return match ($name) {
            'first-page', 'firstPage' => 'firstPage',
            'last-page', 'lastPage' => 'lastPage',
            'odd' => 'odd',
            'even' => 'even',
            'landscape' => 'landscape',
            'portrait' => 'portrait',
            'header' => 'header',
            'footer' => 'footer',
            default => null,
        };
    }
}
