# Template language 2.0

The v2 template language compiles `.folio` files into executable PHP. It is
explicit, declarative and whitespace-insensitive.

```folio
@use "partials/logo.folio"

prop customerName = ""
prop customerEmail = ""
prop invoiceNumber = ""
prop invoiceDate = ""
prop items = []
prop total = "$0.00"

page(background="#ffffff") {
    column(width="100%", padding=48, gap=24) {
        row(gap=24) {
            column(grow=1, gap=4) {
                heading(color="#0f172a", fontSize=24) "Acme Corporation"
                text(color="#64748b", fontSize=10) "123 Business Street, New York, NY 10001"
            }
            column(gap=4, align="right") {
                text(color="#64748b", fontSize=10, fontWeight="bold") "INVOICE"
                heading(color="#0f172a", fontSize=24, align="right") invoiceNumber
            }
        }

        text(color="#334155", fontSize=11) "Date: {invoiceDate}"
        text(color="#334155", fontSize=11) "Customer: {customerName}"
        text(color="#64748b", fontSize=10) customerEmail

        table(padding=12, background="#f8fafc", width="100%") {
            header(background="#0f172a", color="#ffffff", fontSize=10, fontWeight="bold") {
                th "Item"
                th(align="right") "Qty"
                th(align="right") "Price"
                th(align="right") "Total"
            }
            foreach items as item {
                tr(background="#ffffff", fontSize=10) {
                    td item.name
                    td(align="right") item.quantity
                    td(align="right") item.price
                    td(align="right") item.total
                }
            }
        }

        if total != "" {
            text(align="right", color="#0f172a", fontSize=14, fontWeight="bold") "Total: {total}"
        }
    }
}
```

## Directives

### `@use` partial inlining

Inline a partial template. The path is resolved relative to the current template.

```folio
@use "partials/logo.folio"

page {
    column {
        text "Main content"
    }
}
```

### `@theme` design-token loading

Load a JSON theme file that defines design tokens and named styles.

```folio
@theme "modern"

page {
    column {
        text(class="brand") "Hello"
    }
}
```

The theme file is searched next to the template:

```text
modern.json
modern.theme.json
themes/modern.json
```

### `@style` scoped style blocks

Write CSS-like rules that apply to elements by class or element type.

```folio
@style {
    .brand {
        color: #1e3a8a;
        fontSize: 20;
    }

    .card {
        background: #f8fafc;
        padding: 16;
        radius: 4;
    }
}

page {
    column {
        text(class="brand") "Branded title"
        column(class="card") { ... }
    }
}
```

Token references from the loaded theme work inside `@style` blocks:

```folio
@style {
    .brand {
        color: {colors.brand};
        fontSize: {fontSizes.2xl};
    }
}
```

## Partials and reusable headers

`@use` inlines the contents of another `.folio` file, so you can share a header
or footer across documents. Variables in the partial are resolved from the
main template scope.

```folio
// partials/pro-header.folio
column(width="100%", background="{colors.brand}", padding="{space.6}") {
    row {
        column(grow=1) {
            heading(color="{colors.paper}", fontSize="{fontSizes.2xl}") companyName
            text(color="{colors.subtle}", fontSize="{fontSizes.sm}") companyAddress
        }
        column(align="right") {
            text(color="{colors.paper}", fontWeight="bold") documentLabel
            heading(color="{colors.paper}") documentNumber
        }
    }
}
```

```folio
// invoice.folio
prop companyName = ""
prop companyAddress = ""
prop documentLabel = ""
prop documentNumber = ""

@use "partials/pro-header.folio"
@theme "pro"

page {
    ...
}
```

## Putting it together

A full template combines `@use` partials, `@theme` tokens and `@style` rules:

```folio
@use "partials/pro-header.folio"
@theme "pro"

@style {
    .card {
        background: {colors.surface};
        padding: {space.6};
        radius: {radii.lg};
    }
}

page(background="{colors.paper}") {
    column(class="card") {
        heading(class="brand") "Hello, Folio 2.0"
    }
}
```

## Props

Props declare data the template expects. They have an identifier, an optional
type and an optional default value.

```folio
prop title = "Folio"
prop items = []
prop config = {}
```

## Elements

| Element | Purpose |
|---------|---------|
| `page` | A page in the document. Use `size` and `background` attributes. |
| `column` | Vertical flex container. Use `gap`, `padding`, `align`, `width`, `grow`. |
| `row` | Horizontal flex container. Use `gap`, `padding`, `align`, `width`. |
| `text` | A paragraph of text. Supports `color`, `fontSize`, `fontWeight`, `align`. |
| `heading` | A heading. Level is set by `level=N` (default `1`). |
| `table` | A table with `header`/`tr`/`td`/`th` children. Use `padding`, `background`, `width`. |
| `header` | A table header row. |
| `tr` | A table data row. |
| `td` / `th` | Table cells. Use `colspan`, `align`, `background`. |

## String interpolation

Text and heading literals may contain `{variable}` or `{object.property}`
placeholders:

```folio
text "Customer: {customer.name}"
text "Date: {invoiceDate}"
```

## Control flow

### if

```folio
if customer.vip {
    text "VIP customer"
} else {
    text "Standard customer"
}
```

### foreach

```folio
foreach items as item {
    row {
        text item.name
        text(align="right") item.price
    }
}
```

## Styling

Style attributes are passed directly on elements. The value can be a number
(points), a string color hex code, or one of the supported keywords such as
`left` / `right` / `center` / `justify` for `align` and `bold` / `normal` for
`fontWeight`.

```folio
column(padding=24, gap=12, background="#f8fafc", width="100%") {
    text(color="#0f172a", fontSize=12, fontWeight="bold") "Section title"
    text(color="#64748b", fontSize=10) "Section body"
}
```

## Known limitations

- Components with slots are not yet implemented.
- Filter expressions such as `{total | money}` are not yet implemented.
- Gradients and advanced CSS effects are not yet supported.
