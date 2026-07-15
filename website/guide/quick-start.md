# Quick Start

The fastest way to understand Folio is to generate a document. You can use the PHP builder API, a `.folio` template, or both in the same project.

## PHP Builder API

Describe the document structure directly in PHP:

```php
use Folio\Pdf\Document\Pdf;
use Folio\Pdf\Nodes\Column;
use Folio\Pdf\Nodes\Heading;
use Folio\Pdf\Nodes\Page;
use Folio\Pdf\Nodes\Text;

Pdf::make()
    ->page(
        Page::a4()->withContent(
            Column::make(null, [
                Heading::h1('Invoice'),
                Text::make('Customer: John Doe'),
                Text::make('Amount: $100.00'),
            ])
        )
    )
    ->save('invoice.pdf');
```

Every node is immutable. `Column::make(null, [...])` creates a column with a list of children and no style. `Page::a4()->withContent(...)` builds an A4 page. `Pdf::make()` is the fluent entry point.

## Template Language

Create `invoice.folio`:

```folio
var customer = "John Doe"
var amount = "100.00"

page {
  heading "Invoice"
  text "Customer:"
  text customer
  text "Amount:"
  text amount
}
```

Compile and render it from PHP:

```php
use Folio\Pdf\Template\PhpTemplateCompiler;

$compiler = new PhpTemplateCompiler();

$pdf = $compiler->render(file_get_contents('invoice.folio'), [
    'customer' => 'Jane Smith',
    'amount' => '250.00',
]);

$pdf->save('invoice.pdf');
```

`render()` compiles the template to a PHP closure, merges the provided data, and returns a `Pdf` instance ready to save.

## Templates with Control Flow

```folio
var title = "Monthly Report"

page {
  heading title
  foreach products as product {
    column {
      heading product.name
      text product.description
      text product.price
    }
  }
}
```

```php
$pdf = $compiler->render(file_get_contents('report.folio'), [
    'title' => 'Q4 Sales Report',
    'products' => [
        ['name' => 'Product A', 'description' => 'Premium widget', 'price' => '99.00'],
        ['name' => 'Product B', 'description' => 'Standard widget', 'price' => '49.00'],
    ],
]);

$pdf->save('report.pdf');
```

## Page Chrome

Headers and footers are defined as `pageheader` and `pagefooter` blocks. They are rendered on every page unless you scope them with `only`.

```folio
pageheader(height=40) {
  text "Acme Corporation"
  text "123 Business Street"
}

pagefooter(height=30) {
  text "Page:"
  pagenum(format="{page} / {pages}", size=8)
}

page {
  heading "Document"
  text "Content begins here."
}
```

## Next Steps

- [Styling System](./styling.md)
- [API Reference](../api/pdf.md)
- [Template Language](../template-language/overview.md)
