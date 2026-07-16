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
            each item in items {
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

- `@use "path"` — inline a partial template. The path is resolved relative to the
  current template.
- `@theme "name"` — parsed but not yet wired to the runtime style engine.
- `@style { ... }` — parsed but not yet implemented.

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

### each

```folio
each item in items {
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

The proposal also describes design-token themes (`@theme`), `@style` blocks,
components with slots, and filter expressions (`{total | money}`). These are
not yet implemented in the current Folio 2.0 branch and will be added in
subsequent releases.
