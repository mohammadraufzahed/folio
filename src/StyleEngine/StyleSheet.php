<?php

declare(strict_types=1);

namespace Folio\Pdf\StyleEngine;

final class StyleSheet
{
    /** @var array<int, StyleRule> */
    private readonly array $rules;

    public function __construct(StyleRule ...$rules)
    {
        $this->rules = $rules;
    }

    /**
     * @param array<int, StyleRule> $rules
     */
    public static function fromArray(array $rules): self
    {
        return new self(...$rules);
    }

    /**
     * @param array<int, string> $classList
     *
     * @return array<string, mixed>
     */
    public function matchingProperties(string $type, array $classList, StyleContext $context): array
    {
        $properties = [];

        foreach ($this->rules as $rule) {
            if ($rule->matches($type, $classList, $context)) {
                $properties = array_merge($properties, $rule->properties);
            }
        }

        return $properties;
    }

    /**
     * @return array<int, StyleRule>
     */
    public function rules(): array
    {
        return $this->rules;
    }
}
