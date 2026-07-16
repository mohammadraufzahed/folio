# Template language 2.0

The v2 template language adds typed props, `@use` partials, `@theme` support and
a cleaner component syntax.

```folio
@theme "corporate"
@use "partials/header.folio"

prop invoice: Invoice

page {
    header { Header(invoice: invoice) }
    footer { PageNumber(format="Page {page} of {pages}") }

    column(padding=24, gap=12) {
        heading "Invoice #{invoice.number}"
        text "Customer: {invoice.customer.name}"
        table(data=invoice.items, repeatHeader=true) {
            column "Description" { grow 3 }
            column "Qty" { width 40; align right }
            column "Price" { width 80; align right }
        }
    }
}
```

## Directives

- `@theme "name"` — load a theme.
- `@use "path"` — inline a partial.
- `@style` — scoped or global style blocks.

## Props

```folio
prop customer: Customer
prop title: string = "Folio"
```
