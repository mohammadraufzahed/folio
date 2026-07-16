# Styling in Folio 2.0

Folio 2.0 applies styles directly to elements in the template or to PHP node
builders. The style system is intentionally small and explicit: every value is
resolved before layout, so the output is predictable.

## Style attributes

Most elements accept these attributes:

| Attribute | Type | Effect |
|-----------|------|--------|
| `width` | length or `%` | Element width. `100%` fills the parent container. |
| `height` | length or `%` | Element height. |
| `padding` | number | Inner spacing on all sides (points). |
| `gap` | number | Space between children in a `column` or `row`. |
| `background` | color hex | Solid background fill. |
| `color` | color hex | Text color. |
| `fontSize` | number | Text size in points. |
| `fontWeight` | `bold` / `normal` | Currently maps to Helvetica Bold or normal. |
| `align` | `left` / `right` / `center` / `justify` | Text alignment. |
| `grow` | number | Flex grow factor for `column`/`row` children. |
| `colspan` | integer | Table cell column span. |

Lengths are in **points** (1/72 inch) unless you append a `%`, which makes
them relative to the available width.

## Design tokens and themes

Load a theme with `@theme "name"`. A theme is a JSON file that groups values
into categories such as `colors`, `fontSizes`, `space` and `radii`.

```json
{
  "tokens": {
    "colors": {
      "brand": "#1e3a8a",
      "surface": "#f8fafc"
    },
    "fontSizes": {
      "2xl": 20
    },
    "space": {
      "4": 12
    }
  },
  "styles": {
    "brand": {
      "color": "{colors.brand}",
      "fontSize": "{fontSizes.2xl}"
    }
  }
}
```

Reference tokens in attributes or style blocks with `{category.name}`:

```folio
@theme "modern"

page {
    text(class="brand") "Branded heading"
}
```

## `@style` blocks

Add CSS-like rules that apply by class or element type. These are scoped to the
document that contains them.

```folio
@style {
    .hero {
        background: {colors.surface};
        padding: 24;
        color: {colors.brand};
    }

    heading {
        fontWeight: bold;
    }
}

page {
    column {
        heading(class="hero") "Title"
    }
}
```

## Colors

Folio 2.0 accepts 6-digit hex colors:

```folio
text(color="#0f172a") "Dark slate"
text(color="#ef4444") "Red alert"
```

A short palette used across the examples:

- `#0f172a` — dark navy (headers)
- `#334155` — slate (sub-headings)
- `#64748b` — muted slate (secondary text)
- `#94a3b8` — light slate (subtle labels)
- `#f8fafc` — near white (card backgrounds)
- `#ffffff` — white

## Flex layout

`column` and `row` are the two flex containers.

- `column` stacks children vertically.
- `row` places children horizontally.
- `gap` sets the spacing between children.
- `align` on the container controls cross-axis alignment.
- `grow` on a child makes it expand to fill leftover space.

```folio
row(gap=24) {
    column(grow=1) { text "Left side" }
    column(grow=1) { text "Right side" }
}
```

## Text styling

Text inherits the current color and font size from its parent. You can also set
it explicitly:

```folio
column(padding=24, gap=8) {
    heading(fontSize=20, color="#0f172a") "Title"
    text(fontSize=11, color="#64748b") "Body copy in a muted color."
    text(fontSize=11, fontWeight="bold") "Emphasised inline text."
}
```

## Table styling

Tables accept `padding`, `background` and `width`. Rows accept `background`,
`fontSize` and `fontWeight`. Cells accept `align`, `background` and `colspan`.

```folio
table(padding=12, background="#f8fafc", width="100%") {
    header(background="#0f172a", color="#ffffff", fontSize=10, fontWeight="bold") {
        th "Description"
        th(align="right") "Qty"
    }
    tr(background="#ffffff", fontSize=10) {
        td "Premium service"
        td(align="right") "2"
    }
}
```

Style inheritance means `fontWeight="bold"` on the `header` row makes each `th`
inside bold.

## What is not yet supported

PandaCSS-style recipes and slot recipes, gradients, and filters are not yet
implemented.
