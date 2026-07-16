<?php

declare(strict_types=1);

namespace Folio\Pdf\StyleEngine;

use Folio\Pdf\Styling\Color;
use Folio\Pdf\Styling\Length;
use Folio\Pdf\Styling\LengthUnit;

final readonly class TokenSet
{
    /**
     * @param array<string, array<string, mixed>> $categories
     */
    public function __construct(public array $categories = [])
    {
    }

    public static function fromArray(array $data): self
    {
        return new self($data);
    }

    public function get(string $category, string $name): mixed
    {
        return $this->categories[$category][$name] ?? null;
    }

    public function has(string $category, string $name): bool
    {
        return isset($this->categories[$category][$name]);
    }

    public function color(string $name): ?Color
    {
        $value = $this->get('colors', $name);

        if ($value instanceof Color) {
            return $value;
        }

        if (!is_string($value)) {
            return null;
        }

        try {
            return Color::hex($value);
        } catch (\InvalidArgumentException) {
            return null;
        }
    }

    public function length(string $category, string $name): ?Length
    {
        $value = $this->get($category, $name);

        if ($value instanceof Length) {
            return $value;
        }

        if (is_numeric($value)) {
            return Length::pt((float) $value);
        }

        if (!is_string($value)) {
            return null;
        }

        return self::parseLengthString($value);
    }

    public function fontSize(string $name): ?float
    {
        $value = $this->get('fontSizes', $name);

        if (is_numeric($value)) {
            return (float) $value;
        }

        if (!is_string($value)) {
            return null;
        }

        $length = self::parseLengthString($value);

        if ($length !== null && $length->unit() === LengthUnit::Pt) {
            return $length->value();
        }

        return null;
    }

    public static function parseLengthString(string $value): ?Length
    {
        $value = trim($value);

        if (is_numeric($value)) {
            return Length::pt((float) $value);
        }

        if (preg_match('/^([\d.]+)\s*(px|pt|mm|cm|%|fr)$/i', $value, $matches)) {
            $number = (float) $matches[1];
            $unit = strtolower($matches[2]);

            return match ($unit) {
                'px' => Length::px($number),
                'pt' => Length::pt($number),
                'mm' => Length::mm($number),
                'cm' => Length::cm($number),
                '%' => Length::percent($number),
                'fr' => Length::fr($number),
                default => null,
            };
        }

        return null;
    }
}
