# Styling System

The styling system provides strongly typed properties inspired by Flutter.

## PHP Builder API

```php
use Folio\Pdf\Styling\Style;
use Folio\Pdf\Styling\Color;
use Folio\Pdf\Styling\FontWeight;
use Folio\Pdf\Styling\Alignment;

Style::make()
    ->withPadding(20.0)
    ->withMargin(10.0)
    ->withColor(Color::hex('#333333'))
    ->withFontSize(14.0)
    ->withFontWeight(FontWeight::Bold)
    ->withAlignment(Alignment::Center);
```

## Template Language

### Style Directive

Apply styles using the `@style` directive:

```folio
@style padding=20, margin=10 {
  column {
    heading "Styled Heading"
    text "Styled text content"
  }
}
```

### Inline Attributes

Some elements support inline styling attributes:

```folio
page(size=a4, orientation=landscape) {
  heading "Landscape Page"
}
```

## Properties

### Spacing

**PHP API:**
- `withPadding(float $padding)` - Set padding on all sides
- `withMargin(float $margin)` - Set margin on all sides

**Template:**
- `@style padding=20` - Set padding
- `@style margin=10` - Set margin

### Typography

**PHP API:**
- `withFontSize(float $size)` - Set font size
- `withFontWeight(FontWeight $weight)` - Set font weight (Regular, Bold, etc.)
- `withAlignment(Alignment $alignment)` - Set text alignment (Left, Center, Right)

**Template:**
- `@style fontSize=14` - Set font size
- `@style fontWeight=bold` - Set font weight
- `@style alignment=center` - Set text alignment

### Colors

**PHP API:**
- `withColor(Color $color)` - Set text color

**Template:**
- `@style color="#333333"` - Set text color

## Color

Create colors using the `Color` class:

```php
use Folio\Pdf\Styling\Color;

Color::hex('#333333')      // From hex
Color::rgb(51, 51, 51)     // From RGB
Color::rgba(51, 51, 51, 1.0) // From RGBA
```

In templates, use hex color strings:

```folio
@style color="#333333" {
  text "Dark text"
}
```

## Applying Styles

**PHP API:**

```php
Text::make('Hello')->withStyle($style);
Column::make()->withStyle($style);
Heading::h1('Title')->withStyle($style);
```

**Template:**

```folio
@style padding=20, color="#333333" {
  text "Styled text"
  heading "Styled heading"
}
```
