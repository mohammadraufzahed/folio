# Directives

The Folio grammar reserves the `@` prefix for directives. Directives are a future extension point; today, the same outcomes are handled through elements and attributes.

## What Is Reserved?

A directive starts with `@` and has a name followed by an optional block or arguments:

```folio
@import "shared/components.folio"
```

This syntax is recognized by the lexer and parser, but the runtime currently treats directives as no-ops.

## What to Use Today

### Page chrome

Use `pageheader` and `pagefooter` elements instead of `@header` or `@footer`:

```folio
pageheader {
  text "Acme Corporation"
}

pagefooter {
  pagenum(format="Page {page} / {pages}", size=8)
}

page {
  heading "Document"
}
```

### Styling

Apply styles with inline attributes on elements instead of a global `@style` block:

```folio
column(padding=20, margin=10, background="#f8fafc") {
  heading(color="#1e293b", fontSize=18) "Title"
  text(color="#475569", fontSize=11) "Body copy"
}
```

### Imports and partials

At this stage, Folio does not support `@import` or `@include`. You can compose templates from PHP by loading strings and concatenating them, or by rendering multiple templates and merging the resulting `Pdf` instances in your application code.

## Future Directives

Planned directives include `@import` for template composition and `@include` for reusable partials. When they land, they will compile to the same native PHP closures as the rest of the language.
