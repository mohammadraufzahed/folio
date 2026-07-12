# Quick Start

## PHP Builder API

### Basic Example

```php
use Folio\Pdf\Document\Pdf;
use Folio\Pdf\Nodes\Column;
use Folio\Pdf\Nodes\Heading;
use Folio\Pdf\Nodes\Page;
use Folio\Pdf\Nodes\Text;

Pdf::make()
    ->page(
        Page::a4()->withContent(
            Column::make()
                ->addChildren([
                    Heading::h1('Invoice'),
                    Text::make('Customer: John Doe'),
                    Text::make('Amount: $100.00'),
                ])
        )
    )
    ->save('invoice.pdf');
```

## Template Language

### Basic Template

Create `invoice.folio`:

```folio
var customer = "John Doe"
var amount = "100.00"

page {
  heading "Invoice"
  text "Customer: {customer}"
  text "Amount: ${amount}"
}
```

Compile and render:

```php
use Folio\Template\PhpTemplateCompiler;

$compiler = new PhpTemplateCompiler();
$template = $compiler->compile(file_get_contents('invoice.folio'));

$pdf = $template([
    'customer' => 'Jane Smith',
    'amount' => '250.00'
]);
$pdf->save('invoice.pdf');
```

### Template with Control Flow

```folio
var title = "Monthly Report"
var items = []

page {
  heading title
  foreach items as item {
    column {
      heading item.name
      text item.description
      text "Price: ${item.price}"
    }
  }
}
```

Render with data:

```php
$template([
    'title' => 'Q4 Sales Report',
    'items' => [
        ['name' => 'Product A', 'description' => 'Premium widget', 'price' => '99.00'],
        ['name' => 'Product B', 'description' => 'Standard widget', 'price' => '49.00'],
    ]
]);
```

### Template with Directives

```folio
@header {
  text "Acme Corporation"
}

@footer {
  text "Page 1"
}

page {
  heading "Invoice"
  text "Customer: {customer}"
}
```

## Next Steps

- [Styling System](./styling.md)
- [API Reference](../api/pdf.md)
- [Template Language](../template-language/overview.md)
