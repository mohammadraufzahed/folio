<?php

declare(strict_types=1);

namespace Folio\Pdf\Styling;

use Folio\Pdf\Support\Immutable;

final class Style
{
    use Immutable;

    private readonly ?float $padding;
    private readonly ?float $paddingTop;
    private readonly ?float $paddingRight;
    private readonly ?float $paddingBottom;
    private readonly ?float $paddingLeft;
    private readonly ?float $margin;
    private readonly ?float $marginTop;
    private readonly ?float $marginRight;
    private readonly ?float $marginBottom;
    private readonly ?float $marginLeft;
    private readonly ?Border $border;
    private readonly ?float $radius;
    private readonly ?Color $background;
    private readonly ?string $font;
    private readonly ?float $fontSize;
    private readonly ?FontWeight $fontWeight;
    private readonly ?float $lineHeight;
    private readonly ?float $letterSpacing;
    private readonly ?Color $color;
    private readonly ?float $opacity;
    private readonly ?float $rotation;
    private readonly ?float $scale;
    private readonly ?Shadow $shadow;
    private readonly ?Alignment $alignment;
    private readonly ?Flex $flex;
    private readonly ?float $grow;
    private readonly ?float $shrink;
    private readonly ?float $gap;
    private readonly ?Length $width;
    private readonly ?Length $height;
    private readonly ?Length $minWidth;
    private readonly ?Length $maxWidth;
    /** @var list<string> */
    private readonly array $classList;

    private function __construct(array $properties)
    {
        $this->padding = $properties['padding'] ?? null;
        $this->paddingTop = $properties['paddingTop'] ?? null;
        $this->paddingRight = $properties['paddingRight'] ?? null;
        $this->paddingBottom = $properties['paddingBottom'] ?? null;
        $this->paddingLeft = $properties['paddingLeft'] ?? null;
        $this->margin = $properties['margin'] ?? null;
        $this->marginTop = $properties['marginTop'] ?? null;
        $this->marginRight = $properties['marginRight'] ?? null;
        $this->marginBottom = $properties['marginBottom'] ?? null;
        $this->marginLeft = $properties['marginLeft'] ?? null;
        $this->border = $properties['border'] ?? null;
        $this->radius = $properties['radius'] ?? null;
        $this->background = $properties['background'] ?? null;
        $this->font = $properties['font'] ?? null;
        $this->fontSize = $properties['fontSize'] ?? null;
        $this->fontWeight = $properties['fontWeight'] ?? null;
        $this->lineHeight = $properties['lineHeight'] ?? null;
        $this->letterSpacing = $properties['letterSpacing'] ?? null;
        $this->color = $properties['color'] ?? null;
        $this->opacity = $properties['opacity'] ?? null;
        $this->rotation = $properties['rotation'] ?? null;
        $this->scale = $properties['scale'] ?? null;
        $this->shadow = $properties['shadow'] ?? null;
        $this->alignment = $properties['alignment'] ?? null;
        $this->flex = $properties['flex'] ?? null;
        $this->grow = $properties['grow'] ?? null;
        $this->shrink = $properties['shrink'] ?? null;
        $this->gap = $properties['gap'] ?? null;
        $this->width = $properties['width'] ?? null;
        $this->height = $properties['height'] ?? null;
        $this->minWidth = $properties['minWidth'] ?? null;
        $this->maxWidth = $properties['maxWidth'] ?? null;
        $this->classList = $properties['classList'] ?? [];
    }

    public static function make(): self
    {
        return new self([]);
    }

    public function withPadding(?float $value): self
    {
        return new self([...$this->toArray(), 'padding' => $value]);
    }

    public function withMargin(?float $value): self
    {
        return new self([...$this->toArray(), 'margin' => $value]);
    }

    public function withBorder(?Border $border): self
    {
        return new self([...$this->toArray(), 'border' => $border]);
    }

    public function withRadius(?float $value): self
    {
        return new self([...$this->toArray(), 'radius' => $value]);
    }

    public function withBackground(?Color $color): self
    {
        return new self([...$this->toArray(), 'background' => $color]);
    }

    public function withFont(?string $font): self
    {
        return new self([...$this->toArray(), 'font' => $font]);
    }

    public function withFontSize(?float $size): self
    {
        return new self([...$this->toArray(), 'fontSize' => $size]);
    }

    public function withFontWeight(?FontWeight $weight): self
    {
        return new self([...$this->toArray(), 'fontWeight' => $weight]);
    }

    public function withLineHeight(?float $value): self
    {
        return new self([...$this->toArray(), 'lineHeight' => $value]);
    }

    public function withColor(?Color $color): self
    {
        return new self([...$this->toArray(), 'color' => $color]);
    }

    public function withOpacity(?float $value): self
    {
        return new self([...$this->toArray(), 'opacity' => $value]);
    }

    public function withShadow(?Shadow $value): self
    {
        return new self([...$this->toArray(), 'shadow' => $value]);
    }

    public function withGap(?float $value): self
    {
        return new self([...$this->toArray(), 'gap' => $value]);
    }

    public function withGrow(?float $value): self
    {
        return new self([...$this->toArray(), 'grow' => $value]);
    }

    public function withShrink(?float $value): self
    {
        return new self([...$this->toArray(), 'shrink' => $value]);
    }

    public function withAlignment(?Alignment $alignment): self
    {
        return new self([...$this->toArray(), 'alignment' => $alignment]);
    }

    public function withWidth(?Length $width): self
    {
        return new self([...$this->toArray(), 'width' => $width]);
    }

    public function withHeight(?Length $height): self
    {
        return new self([...$this->toArray(), 'height' => $height]);
    }

    public function toArray(): array
    {
        return get_object_vars($this);
    }

    /**
     * @param string|list<string> $class
     */
    public function withClass(string|array $class): self
    {
        if (is_string($class)) {
            $class = preg_split('/\s+/', trim($class), -1, PREG_SPLIT_NO_EMPTY) ?: [];
        }

        return new self([...$this->toArray(), 'classList' => array_values(array_unique(array_merge($this->classList, $class)))]);
    }

    /**
     * @return list<string>
     */
    public function classList(): array
    {
        return $this->classList;
    }

    public function padding(): ?float
    {
        return $this->padding;
    }
    public function margin(): ?float
    {
        return $this->margin;
    }
    public function marginTop(): ?float
    {
        return $this->marginTop ?? $this->margin;
    }
    public function marginBottom(): ?float
    {
        return $this->marginBottom ?? $this->margin;
    }
    public function marginLeft(): ?float
    {
        return $this->marginLeft ?? $this->margin;
    }
    public function marginRight(): ?float
    {
        return $this->marginRight ?? $this->margin;
    }
    public function paddingTop(): ?float
    {
        return $this->paddingTop ?? $this->padding;
    }
    public function paddingBottom(): ?float
    {
        return $this->paddingBottom ?? $this->padding;
    }
    public function paddingLeft(): ?float
    {
        return $this->paddingLeft ?? $this->padding;
    }
    public function paddingRight(): ?float
    {
        return $this->paddingRight ?? $this->padding;
    }
    public function border(): ?Border
    {
        return $this->border;
    }
    public function radius(): ?float
    {
        return $this->radius;
    }
    public function background(): ?Color
    {
        return $this->background;
    }
    public function font(): ?string
    {
        return $this->font;
    }
    public function fontSize(): ?float
    {
        return $this->fontSize;
    }
    public function fontWeight(): ?FontWeight
    {
        return $this->fontWeight;
    }
    public function lineHeight(): ?float
    {
        return $this->lineHeight;
    }
    public function letterSpacing(): ?float
    {
        return $this->letterSpacing;
    }
    public function color(): ?Color
    {
        return $this->color;
    }
    public function opacity(): ?float
    {
        return $this->opacity;
    }
    public function rotation(): ?float
    {
        return $this->rotation;
    }
    public function scale(): ?float
    {
        return $this->scale;
    }
    public function shadow(): ?Shadow
    {
        return $this->shadow;
    }
    public function alignment(): ?Alignment
    {
        return $this->alignment;
    }
    public function flex(): ?Flex
    {
        return $this->flex;
    }
    public function grow(): ?float
    {
        return $this->grow;
    }
    public function shrink(): ?float
    {
        return $this->shrink;
    }
    public function gap(): ?float
    {
        return $this->gap;
    }
    public function width(): ?Length
    {
        return $this->width;
    }
    public function height(): ?Length
    {
        return $this->height;
    }
    public function minWidth(): ?Length
    {
        return $this->minWidth;
    }
    public function maxWidth(): ?Length
    {
        return $this->maxWidth;
    }
}
