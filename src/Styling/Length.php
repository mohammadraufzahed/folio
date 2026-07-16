<?php

declare(strict_types=1);

namespace Folio\Pdf\Styling;

use Folio\Pdf\Support\Immutable;

final class Length
{
    use Immutable;

    private readonly float $value;
    private readonly LengthUnit $unit;

    private function __construct(float $value, LengthUnit $unit)
    {
        $this->value = $value;
        $this->unit = $unit;
    }

    public static function px(float $value): self
    {
        return new self($value, LengthUnit::Px);
    }

    public static function mm(float $value): self
    {
        return new self($value, LengthUnit::Mm);
    }

    public static function cm(float $value): self
    {
        return new self($value, LengthUnit::Cm);
    }

    public static function pt(float $value): self
    {
        return new self($value, LengthUnit::Pt);
    }

    public static function percent(float $value): self
    {
        return new self($value, LengthUnit::Percent);
    }

    public static function fr(float $value): self
    {
        return new self($value, LengthUnit::Fr);
    }

    public static function auto(): self
    {
        return new self(0, LengthUnit::Auto);
    }

    public function value(): float
    {
        return $this->value;
    }

    public function unit(): LengthUnit
    {
        return $this->unit;
    }

    public function isAuto(): bool
    {
        return $this->unit === LengthUnit::Auto;
    }

    public function toPixels(float $dpi = 72.0): float
    {
        return match ($this->unit) {
            LengthUnit::Px => $this->value,
            LengthUnit::Pt => $this->value * $dpi / 72.0,
            LengthUnit::Mm => $this->value * $dpi / 25.4,
            LengthUnit::Cm => $this->value * $dpi / 2.54,
            LengthUnit::Percent, LengthUnit::Fr, LengthUnit::Auto => $this->value,
        };
    }
}

enum LengthUnit: string
{
    case Px = 'px';
    case Pt = 'pt';
    case Mm = 'mm';
    case Cm = 'cm';
    case Percent = '%';
    case Fr = 'fr';
    case Auto = 'auto';
}
