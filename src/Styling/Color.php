<?php

declare(strict_types=1);

namespace Folio\Pdf\Styling;

use Folio\Pdf\Support\Immutable;

/**
 * Immutable color representation.
 */
final class Color
{
    use Immutable;

    private readonly float $red;
    private readonly float $green;
    private readonly float $blue;
    private readonly float $alpha;

    private function __construct(float $red, float $green, float $blue, float $alpha = 1.0)
    {
        $this->red = max(0.0, min(1.0, $red));
        $this->green = max(0.0, min(1.0, $green));
        $this->blue = max(0.0, min(1.0, $blue));
        $this->alpha = max(0.0, min(1.0, $alpha));
    }

    public static function rgb(float $red, float $green, float $blue): self
    {
        return new self($red / 255, $green / 255, $blue / 255);
    }

    public static function rgba(float $red, float $green, float $blue, float $alpha): self
    {
        return new self($red / 255, $green / 255, $blue / 255, $alpha);
    }

    public static function hex(string $hex): self
    {
        $hex = ltrim($hex, '#');

        if (strlen($hex) === 3) {
            $hex = $hex[0] . $hex[0] . $hex[1] . $hex[1] . $hex[2] . $hex[2];
        }

        if (strlen($hex) === 6) {
            return self::rgb(
                hexdec(substr($hex, 0, 2)),
                hexdec(substr($hex, 2, 2)),
                hexdec(substr($hex, 4, 2))
            );
        }

        if (strlen($hex) === 8) {
            return self::rgba(
                hexdec(substr($hex, 0, 2)),
                hexdec(substr($hex, 2, 2)),
                hexdec(substr($hex, 4, 2)),
                hexdec(substr($hex, 6, 2)) / 255
            );
        }

        throw new \InvalidArgumentException("Invalid hex color: {$hex}");
    }

    public static function black(): self
    {
        return new self(0.0, 0.0, 0.0);
    }

    public static function white(): self
    {
        return new self(1.0, 1.0, 1.0);
    }

    public static function gray(float $value): self
    {
        $normalized = max(0.0, min(1.0, $value));
        return new self($normalized, $normalized, $normalized);
    }

    public function red(): float
    {
        return $this->red;
    }

    public function green(): float
    {
        return $this->green;
    }

    public function blue(): float
    {
        return $this->blue;
    }

    public function alpha(): float
    {
        return $this->alpha;
    }

    public function toRgbArray(): array
    {
        return [
            'r' => round($this->red * 255),
            'g' => round($this->green * 255),
            'b' => round($this->blue * 255),
        ];
    }

    public function toRgbaArray(): array
    {
        return [
            'r' => round($this->red * 255),
            'g' => round($this->green * 255),
            'b' => round($this->blue * 255),
            'a' => $this->alpha,
        ];
    }

    public function toHex(): string
    {
        return sprintf(
            '#%02x%02x%02x',
            round($this->red * 255),
            round($this->green * 255),
            round($this->blue * 255)
        );
    }
}
