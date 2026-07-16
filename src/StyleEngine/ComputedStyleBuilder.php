<?php

declare(strict_types=1);

namespace Folio\Pdf\StyleEngine;

use Folio\Pdf\Styling\Alignment;
use Folio\Pdf\Styling\Border;
use Folio\Pdf\Styling\Color;
use Folio\Pdf\Styling\FontWeight;
use Folio\Pdf\Styling\Length;
use Folio\Pdf\Styling\Shadow;

final class ComputedStyleBuilder
{
    /** @var array<string, mixed> */
    private array $box = [];

    /** @var array<string, mixed> */
    private array $text = [];

    /** @var array<string, mixed> */
    private array $layout = [];

    /** @var array<string, mixed> */
    private array $paint = [];

    public static function fromComputed(ComputedStyle $style): self
    {
        $builder = new self();
        $builder->box = self::styleToArray($style->box);
        $builder->text = self::styleToArray($style->text);
        $builder->layout = self::styleToArray($style->layout);
        $builder->paint = self::styleToArray($style->paint);

        return $builder;
    }

    public static function fromStyle(\Folio\Pdf\Styling\Style $style): self
    {
        $builder = new self();
        $builder->box = [
            'padding' => $style->padding(),
            'margin' => $style->margin(),
            'border' => $style->border(),
            'radius' => $style->radius(),
            'background' => $style->background(),
            'shadow' => $style->shadow(),
            'width' => $style->width(),
            'height' => $style->height(),
        ];
        $builder->text = [
            'font' => $style->font(),
            'fontSize' => $style->fontSize(),
            'fontWeight' => $style->fontWeight(),
            'color' => $style->color(),
            'lineHeight' => $style->lineHeight(),
            'letterSpacing' => $style->letterSpacing(),
            'alignment' => $style->alignment(),
        ];
        $builder->layout = [
            'width' => $style->width(),
            'height' => $style->height(),
            'minWidth' => $style->minWidth(),
            'maxWidth' => $style->maxWidth(),
            'grow' => $style->grow(),
            'shrink' => $style->shrink(),
            'gap' => $style->gap(),
        ];
        $builder->paint = [
            'fill' => $style->background(),
            'opacity' => $style->opacity(),
        ];

        return $builder;
    }

    public function withPaddingTop(?float $value): self
    {
        $this->box['paddingTop'] = $value;
        return $this;
    }
    public function withPaddingRight(?float $value): self
    {
        $this->box['paddingRight'] = $value;
        return $this;
    }
    public function withPaddingBottom(?float $value): self
    {
        $this->box['paddingBottom'] = $value;
        return $this;
    }
    public function withPaddingLeft(?float $value): self
    {
        $this->box['paddingLeft'] = $value;
        return $this;
    }
    public function withMarginTop(?float $value): self
    {
        $this->box['marginTop'] = $value;
        return $this;
    }
    public function withMarginRight(?float $value): self
    {
        $this->box['marginRight'] = $value;
        return $this;
    }
    public function withMarginBottom(?float $value): self
    {
        $this->box['marginBottom'] = $value;
        return $this;
    }
    public function withMarginLeft(?float $value): self
    {
        $this->box['marginLeft'] = $value;
        return $this;
    }

    public function withPadding(?float $value): self
    {
        $this->box['padding'] = $value;

        return $this
            ->withPaddingTop($value)
            ->withPaddingRight($value)
            ->withPaddingBottom($value)
            ->withPaddingLeft($value);
    }

    public function withMargin(?float $value): self
    {
        $this->box['margin'] = $value;

        return $this
            ->withMarginTop($value)
            ->withMarginRight($value)
            ->withMarginBottom($value)
            ->withMarginLeft($value);
    }

    public function withBorder(?Border $value): self
    {
        $this->box['border'] = $value;
        return $this;
    }
    public function withBackground(?Color $value): self
    {
        $this->box['background'] = $value;
        $this->paint['fill'] = $value;
        return $this;
    }
    public function withRadius(?float $value): self
    {
        $this->box['radius'] = $value;
        return $this;
    }
    public function withShadow(?Shadow $value): self
    {
        $this->box['shadow'] = $value;
        return $this;
    }
    public function withWidth(?Length $value): self
    {
        $this->box['width'] = $value;
        $this->layout['width'] = $value;
        return $this;
    }
    public function withHeight(?Length $value): self
    {
        $this->box['height'] = $value;
        $this->layout['height'] = $value;
        return $this;
    }
    public function withMinWidth(?Length $value): self
    {
        $this->layout['minWidth'] = $value;
        return $this;
    }
    public function withMaxWidth(?Length $value): self
    {
        $this->layout['maxWidth'] = $value;
        return $this;
    }
    public function withColor(?Color $value): self
    {
        $this->text['color'] = $value;
        return $this;
    }
    public function withFontSize(?float $value): self
    {
        $this->text['fontSize'] = $value;
        return $this;
    }
    public function withFontWeight(?FontWeight $value): self
    {
        $this->text['fontWeight'] = $value;
        return $this;
    }
    public function withFont(?string $value): self
    {
        $this->text['font'] = $value;
        return $this;
    }
    public function withLineHeight(?float $value): self
    {
        $this->text['lineHeight'] = $value;
        return $this;
    }
    public function withLetterSpacing(?float $value): self
    {
        $this->text['letterSpacing'] = $value;
        return $this;
    }
    public function withAlignment(?Alignment $value): self
    {
        $this->text['alignment'] = $value;
        return $this;
    }
    public function withTextDecoration(?string $value): self
    {
        $this->text['textDecoration'] = $value;
        return $this;
    }
    public function withTextTransform(?string $value): self
    {
        $this->text['textTransform'] = $value;
        return $this;
    }
    public function withGrow(?float $value): self
    {
        $this->layout['grow'] = $value;
        return $this;
    }
    public function withShrink(?float $value): self
    {
        $this->layout['shrink'] = $value;
        return $this;
    }
    public function withDisplay(?string $value): self
    {
        $this->layout['display'] = $value;
        return $this;
    }
    public function withDirection(?string $value): self
    {
        $this->layout['direction'] = $value;
        return $this;
    }
    public function withGap(?float $value): self
    {
        $this->layout['gap'] = $value;
        return $this;
    }
    public function withJustifyContent(?string $value): self
    {
        $this->layout['justifyContent'] = $value;
        return $this;
    }
    public function withAlignItems(?string $value): self
    {
        $this->layout['alignItems'] = $value;
        return $this;
    }
    public function withOpacity(?float $value): self
    {
        $this->paint['opacity'] = $value;
        return $this;
    }

    public function apply(array $properties, ?TokenSet $tokens = null): self
    {
        foreach ($properties as $key => $value) {
            $this->applyProperty((string) $key, $value, $tokens);
        }

        return $this;
    }

    public function build(): ComputedStyle
    {
        return new ComputedStyle(
            new BoxStyle(
                padding: $this->box['padding'] ?? null,
                paddingTop: $this->box['paddingTop'] ?? null,
                paddingRight: $this->box['paddingRight'] ?? null,
                paddingBottom: $this->box['paddingBottom'] ?? null,
                paddingLeft: $this->box['paddingLeft'] ?? null,
                margin: $this->box['margin'] ?? null,
                marginTop: $this->box['marginTop'] ?? null,
                marginRight: $this->box['marginRight'] ?? null,
                marginBottom: $this->box['marginBottom'] ?? null,
                marginLeft: $this->box['marginLeft'] ?? null,
                border: $this->box['border'] ?? null,
                radius: $this->box['radius'] ?? null,
                background: $this->box['background'] ?? null,
                shadow: $this->box['shadow'] ?? null,
                width: $this->box['width'] ?? null,
                height: $this->box['height'] ?? null,
            ),
            new TextStyle(
                font: $this->text['font'] ?? null,
                fontSize: $this->text['fontSize'] ?? null,
                fontWeight: $this->text['fontWeight'] ?? null,
                color: $this->text['color'] ?? null,
                lineHeight: $this->text['lineHeight'] ?? null,
                letterSpacing: $this->text['letterSpacing'] ?? null,
                alignment: $this->text['alignment'] ?? null,
                textDecoration: $this->text['textDecoration'] ?? null,
                textTransform: $this->text['textTransform'] ?? null,
            ),
            new LayoutStyle(
                display: $this->layout['display'] ?? null,
                direction: $this->layout['direction'] ?? null,
                justifyContent: $this->layout['justifyContent'] ?? null,
                alignItems: $this->layout['alignItems'] ?? null,
                gap: $this->layout['gap'] ?? null,
                width: $this->layout['width'] ?? null,
                height: $this->layout['height'] ?? null,
                minWidth: $this->layout['minWidth'] ?? null,
                maxWidth: $this->layout['maxWidth'] ?? null,
                grow: $this->layout['grow'] ?? null,
                shrink: $this->layout['shrink'] ?? null,
            ),
            new PaintStyle(
                fill: $this->paint['fill'] ?? null,
                opacity: $this->paint['opacity'] ?? null,
            ),
        );
    }

    private function applyProperty(string $key, mixed $value, ?TokenSet $tokens): void
    {
        $map = [
            'padding' => 'withPadding',
            'margin' => 'withMargin',
            'background' => 'withBackground',
            'color' => 'withColor',
            'fontSize' => 'withFontSize',
            'fontWeight' => 'withFontWeight',
            'font' => 'withFont',
            'lineHeight' => 'withLineHeight',
            'letterSpacing' => 'withLetterSpacing',
            'align' => 'withAlignment',
            'alignment' => 'withAlignment',
            'radius' => 'withRadius',
            'width' => 'withWidth',
            'height' => 'withHeight',
            'minWidth' => 'withMinWidth',
            'maxWidth' => 'withMaxWidth',
            'grow' => 'withGrow',
            'shrink' => 'withShrink',
            'opacity' => 'withOpacity',
            'shadow' => 'withShadow',
            'border' => 'withBorder',
            'display' => 'withDisplay',
            'direction' => 'withDirection',
            'gap' => 'withGap',
            'justifyContent' => 'withJustifyContent',
            'alignItems' => 'withAlignItems',
            'textDecoration' => 'withTextDecoration',
            'textTransform' => 'withTextTransform',
        ];

        if ($key === 'padding' && is_string($value)) {
            $parsed = ShorthandParser::padding($value, $tokens);

            $this->withPaddingTop($parsed['top']);
            $this->withPaddingRight($parsed['right']);
            $this->withPaddingBottom($parsed['bottom']);
            $this->withPaddingLeft($parsed['left']);

            return;
        }

        if ($key === 'margin' && is_string($value)) {
            $parsed = ShorthandParser::padding($value, $tokens);

            $this->withMarginTop($parsed['top']);
            $this->withMarginRight($parsed['right']);
            $this->withMarginBottom($parsed['bottom']);
            $this->withMarginLeft($parsed['left']);

            return;
        }

        if (isset($map[$key])) {
            if (is_string($value)) {
                $this->applyStringProperty($key, $value, $tokens);
            } else {
                $method = $map[$key];
                $this->$method($value);
            }
        }
    }

    private function applyStringProperty(string $key, string $value, ?TokenSet $tokens): void
    {
        $methodMap = [
            'padding' => 'withPadding',
            'margin' => 'withMargin',
            'background' => 'withBackground',
            'color' => 'withColor',
            'fontSize' => 'withFontSize',
            'fontWeight' => 'withFontWeight',
            'font' => 'withFont',
            'lineHeight' => 'withLineHeight',
            'letterSpacing' => 'withLetterSpacing',
            'align' => 'withAlignment',
            'alignment' => 'withAlignment',
            'radius' => 'withRadius',
            'width' => 'withWidth',
            'height' => 'withHeight',
            'minWidth' => 'withMinWidth',
            'maxWidth' => 'withMaxWidth',
            'grow' => 'withGrow',
            'shrink' => 'withShrink',
            'opacity' => 'withOpacity',
            'display' => 'withDisplay',
            'direction' => 'withDirection',
            'gap' => 'withGap',
            'justifyContent' => 'withJustifyContent',
            'alignItems' => 'withAlignItems',
            'textDecoration' => 'withTextDecoration',
            'textTransform' => 'withTextTransform',
        ];

        if ($key === 'padding' || $key === 'margin') {
            $parsed = ShorthandParser::padding($value, $tokens);
            $prefix = $key === 'padding' ? 'withPadding' : 'withMargin';
            $this->{$prefix . 'Top'}($parsed['top']);
            $this->{$prefix . 'Right'}($parsed['right']);
            $this->{$prefix . 'Bottom'}($parsed['bottom']);
            $this->{$prefix . 'Left'}($parsed['left']);

            return;
        }

        $resolved = match ($key) {
            'background', 'color' => ShorthandParser::color($value, $tokens),
            'fontSize', 'radius', 'lineHeight', 'letterSpacing', 'gap' => ShorthandParser::length($value, $tokens, self::categoryFor($key))?->toPixels(),
            'width', 'height', 'minWidth', 'maxWidth' => ShorthandParser::length($value, $tokens),
            'fontWeight' => $this->fontWeight($value),
            'opacity' => is_numeric($value) ? (float) $value : null,
            'shadow' => ShorthandParser::shadow($value, $tokens),
            'border' => ShorthandParser::border($value, $tokens),
            'alignment', 'align' => $this->alignment($value),
            default => $value,
        };

        if ($resolved !== null && isset($methodMap[$key])) {
            $this->{$methodMap[$key]}($resolved);
        }
    }

    private function categoryFor(string $key): string
    {
        return match ($key) {
            'fontSize' => 'fontSizes',
            'radius' => 'radii',
            'lineHeight' => 'lineHeights',
            'letterSpacing' => 'letterSpacings',
            default => 'space',
        };
    }

    private function fontWeight(string $value): ?FontWeight
    {
        return match ($value) {
            'thin', '100' => FontWeight::Thin,
            'extralight', '200' => FontWeight::ExtraLight,
            'light', '300' => FontWeight::Light,
            'normal', '400' => FontWeight::Regular,
            'medium', '500' => FontWeight::Medium,
            'semibold', '600' => FontWeight::SemiBold,
            'bold', '700' => FontWeight::Bold,
            'extrabold', '800' => FontWeight::ExtraBold,
            'black', '900' => FontWeight::Black,
            default => null,
        };
    }

    private function alignment(string $value): ?Alignment
    {
        return match (strtolower($value)) {
            'left' => Alignment::Left,
            'center' => Alignment::Center,
            'right' => Alignment::Right,
            'justify' => Alignment::Justify,
            default => null,
        };
    }

    /**
     * @return array<string, mixed>
     */
    private static function styleToArray(object $style): array
    {
        return get_object_vars($style);
    }
}
