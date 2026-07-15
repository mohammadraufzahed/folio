# Elements

Folio templates are built from a small set of elements. Every element produces a typed document node that the layout engine understands.

## Page

A page is the top-level container. It sets the physical size and orientation and holds one content node, usually a `column`.

```folio
page {
  heading "Title"
  text "Content"
}

page(size=a3) {
  heading "Large Page"
}

page(size=letter, orientation=landscape) {
  heading "Landscape Letter"
}

page(size="600x800") {
  heading "Custom Size"
}
```

## Heading

Headings support six levels. The default level is 1.

```folio
heading "Main Title"
heading(fontSize=24) "Subtitle"
heading "Section"
```

In the PHP builder, use `Heading::h1()` through `Heading::h6()`.

## Text

Text is the basic content element. It accepts a string or a variable reference as its child.

```folio
text "Hello, world!"
text customerName
```

Use attributes to style the text:

```folio
text(color="#334155", fontSize=11) "Body copy"
text(align=center, fontWeight=bold) "Centered bold text"
```

## Column

A vertical container that stacks children from top to bottom.

```folio
column {
  heading "Section"
  text "First paragraph"
  text "Second paragraph"
}
```

With spacing:

```folio
column(padding=20, margin=10) {
  heading "Section"
  text "Content"
}
```

## Row

A horizontal container that lays out children side by side.

```folio
row(align=center) {
  text "Left"
  spacer
  text "Right"
}
```

`spacer` is a special void element that expands to fill available horizontal space inside a `row`.

## Table

Tables use `table`, `header`, `tr`, `th`, and `td` elements.

```folio
table(showBorders=true) {
  header {
    th "Product"
    th "Price"
  }
  foreach products as product {
    tr {
      td product.name
      td product.price
    }
  }
}
```

Use `columnWidths` to define fixed column widths in points:

```folio
table(columnWidths=[200, 80], showBorders=true) {
  header { ... }
}
```

## Page Chrome

`pageheader` and `pagefooter` define content that repeats on every page. They are elements, not directives.

```folio
pageheader(height=40) {
  text "Acme Corporation"
}

pagefooter(height=30) {
  text "Page:"
  pagenum(format="{page} / {pages}", size=8)
}

page {
  heading "Document"
}
```

`pagenum` is a void element that renders the current and total page numbers using the provided format string.

## Void Elements

`spacer` and `pagenum` have no closing block. Use them inside `row`, `pageheader`, or `pagefooter`.

```folio
row {
  text "Left aligned"
  spacer
  pagenum(format="Page {page}", size=8)
}
```
