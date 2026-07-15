# Examples and Tutorials

This document provides comprehensive examples and tutorials for using Folio PDF.

## Table of Contents

- [Basic Examples](#basic-examples)
- [Intermediate Examples](#intermediate-examples)
- [Advanced Examples](#advanced-examples)
- [Template Language Examples](#template-language-examples)
- [Common Patterns](#common-patterns)
- [Best Practices](#best-practices)

## Basic Examples

### Hello World

The simplest PDF document:

```php
<?php

require_once 'vendor/autoload.php';

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
                    Heading::h1('Hello, World!'),
                    Text::make('This is your first PDF with Folio PDF.'),
                ])
        )
    )
    ->save('hello-world.pdf');
```

### Simple Invoice

A basic invoice with text content:

```php
<?php

require_once 'vendor/autoload.php';

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
                    Heading::h1('Invoice #12345'),
                    Text::make('Date: 2024-01-15'),
                    Text::make(''),
                    Text::make('Bill To: John Doe'),
                    Text::make('Amount: $1,500.00'),
                ])
        )
    )
    ->save('invoice.pdf');
```

### Multiple Pages

Creating a document with multiple pages:

```php
<?php

Pdf::make()
    ->page(Page::a4()->withContent($content1))
    ->page(Page::a4()->withContent($content2))
    ->page(Page::letter()->withContent($content3))
    ->save('multi-page.pdf');
```

## Intermediate Examples

### Styled Document

Using the styling system:

```php
<?php

require_once 'vendor/autoload.php';

use Folio\Pdf\Document\Pdf;
use Folio\Pdf\Nodes\Column;
use Folio\Pdf\Nodes\Heading;
use Folio\Pdf\Nodes\Page;
use Folio\Pdf\Nodes\Text;
use Folio\Pdf\Styling\Color;
use Folio\Pdf\Styling\FontWeight;
use Folio\Pdf\Styling\Style;

$style = Style::make()
    ->withPadding(20.0)
    ->withColor(Color::hex('#333333'))
    ->withFontSize(14.0);

$headingStyle = Style::make()
    ->withColor(Color::hex('#1a1a1a'))
    ->withFontWeight(FontWeight::Bold)
    ->withFontSize(24.0);

Pdf::make()
    ->page(
        Page::a4()->withContent(
            Column::make()
                ->addChildren([
                    Heading::h1('Styled Document')->withStyle($headingStyle),
                    Text::make('This text has custom styling.')->withStyle($style),
                ])
        )
    )
    ->save('styled.pdf');
```

### Row and Column Layouts

Using layout containers:

```php
<?php

use Folio\Pdf\Nodes\Column;
use Folio\Pdf\Nodes\Row;
use Folio\Pdf\Nodes\Text;

$content = Column::make()
    ->addChildren([
        Heading::h1('Two Column Layout'),
        Row::make()->addChildren([
            Column::make()->addChildren([
                Text::make('Left column content'),
                Text::make('More left content'),
            ]),
            Column::make()->addChildren([
                Text::make('Right column content'),
                Text::make('More right content'),
            ]),
        ]),
    ]);

Pdf::make()->page(Page::a4()->withContent($content))->save('layout.pdf');
```

### Simple Table

Creating a table:

```php
<?php

use Folio\Pdf\Nodes\Table;
use Folio\Pdf\Nodes\TableCell;
use Folio\Pdf\Nodes\TableRow;

$table = Table::simple([
    TableRow::make([
        TableCell::make('Name'),
        TableCell::make('Email'),
        TableCell::make('Phone'),
    ]),
    TableRow::make([
        TableCell::make('John Doe'),
        TableCell::make('john@example.com'),
        TableCell::make('555-1234'),
    ]),
    TableRow::make([
        TableCell::make('Jane Smith'),
        TableCell::make('jane@example.com'),
        TableCell::make('555-5678'),
    ]),
]);

Pdf::make()->page(Page::a4()->withContent($table))->save('table.pdf');
```

### Page Headers and Footers

Adding branded headers and footers:

```php
<?php

Pdf::make()
    ->pageHeader([
        'title' => 'Quarterly Report',
        'subtitle' => 'Q4 2024',
        'badge' => 'CONFIDENTIAL',
        'theme' => 'navy',
    ])
    ->pageFooter([
        'left' => 'Acme Corporation',
        'right' => 'Page {page} of {pages}',
        'showPageNumber' => true,
    ])
    ->page(Page::a4()->withContent($content))
    ->save('report.pdf');
```

## Advanced Examples

### Custom Styled Table

Table with custom column widths and styling:

```php
<?php

use Folio\Pdf\Nodes\Table;
use Folio\Pdf\Nodes\TableCell;
use Folio\Pdf\Nodes\TableRow;
use Folio\Pdf\Styling\Color;
use Folio\Pdf\Styling\Style;

$headerStyle = Style::make()
    ->withColor(Color::white())
    ->withBackground(Color::hex('#1a1a1a'))
    ->withPadding(10.0);

$table = Table::withColumnWidths([
    TableRow::make([
        TableCell::make('ID')->withStyle($headerStyle),
        TableCell::make('Product')->withStyle($headerStyle),
        TableCell::make('Price')->withStyle($headerStyle),
    ]),
    TableRow::make([
        TableCell::make('1'),
        TableCell::make('Widget'),
        TableCell::make('$29.99'),
    ]),
], [50.0, 200.0, 100.0]);

Pdf::make()->page(Page::a4()->withContent($table))->save('styled-table.pdf');
```

### Nested Tables

Tables within tables:

```php
<?php

$innerTable = Table::simple([
    TableRow::make([
        TableCell::make('A1'),
        TableCell::make('A2'),
    ]),
    TableRow::make([
        TableCell::make('B1'),
        TableCell::make('B2'),
    ]),
]);

$outerTable = Table::simple([
    TableRow::make([
        TableCell::make('Main Data'),
        TableCell::make('Sub Data'),
    ]),
    TableRow::make([
        TableCell::make('Nested:'),
        TableCell::make('')->withContent($innerTable),
    ]),
]);
```

### Multi-Level Tables

Tables with header rows spanning multiple levels:

```php
<?php

$table = Table::simple([
    TableRow::make([
        TableCell::make('Category'),
        TableCell::make('Q1'),
        TableCell::make('Q2'),
        TableCell::make('Q3'),
        TableCell::make('Q4'),
    ]),
    TableRow::make([
        TableCell::make('Revenue'),
        TableCell::make('$10,000'),
        TableCell::make('$12,000'),
        TableCell::make('$15,000'),
        TableCell::make('$18,000'),
    ]),
]);
```

### Custom Page Size

Creating a PDF with custom dimensions:

```php
<?php

Pdf::make()
    ->page(
        Page::make(600.0, 800.0)->withContent($content)
    )
    ->save('custom-size.pdf');
```

### Landscape Orientation

Creating landscape pages:

```php
<?php

// A4 landscape: 842 x 595 points
Pdf::make()
    ->page(
        Page::make(842.0, 595.0)->withContent($content)
    )
    ->save('landscape.pdf');
```

## Template Language Examples

### Basic Template

```folio
var title = "My Document"

page {
  heading title
  text "This is generated from a template."
}
```

### Template with Variables

```folio
var title = "Invoice #12345"
var customer = "Acme Corporation"
var amount = 1500.00

page {
  heading title
  text "Customer: " customer
  text "Amount: $" amount
}
```

### Conditional Rendering

```folio
var status = "active"

if status == "active" {
  text "Account is active"
} else {
  text "Account is inactive"
}
```

### Loops

```folio
var items = [
  { name: "Item 1", price: 10.00 },
  { name: "Item 2", price: 20.00 }
]

page {
  heading "Items"
  foreach items as item {
    text item.name " - $" item.price
  } empty {
    text "No items available"
  }
}
```

### Template with Styling

```folio
var title = "Styled Document"

page {
  heading(fontSize=24, color=navy) title
  text(fontSize=14, color=gray) "This text is styled"
}
```

### Template Partials

```folio
// main.folio
partial "header" (title="Report")

page {
  partial "footer"
  text "Content here"
}
```

## Common Patterns

### Reusable Components

Create reusable component functions:

```php
function createSection(string $title, string $content): Column
{
    return Column::make()
        ->addChildren([
            Heading::h2($title),
            Text::make($content),
        ]);
}

// Usage
Pdf::make()
    ->page(
        Page::a4()->withContent(
            Column::make()
                ->addChildren([
                    createSection('Introduction', 'Intro text'),
                    createSection('Methodology', 'Method text'),
                    createSection('Results', 'Results text'),
                ])
        )
    )
    ->save('report.pdf');
```

### Document Builder Pattern

Create a document builder for complex documents:

```php
class ReportBuilder
{
    private array $sections = [];
    
    public function addSection(string $title, string $content): self
    {
        $this->sections[] = [
            'title' => $title,
            'content' => $content,
        ];
        return $this;
    }
    
    public function build(): Pdf
    {
        $content = Column::make();
        
        foreach ($this->sections as $section) {
            $content = $content->addChildren([
                Heading::h2($section['title']),
                Text::make($section['content']),
            ]);
        }
        
        return Pdf::make()
            ->pageHeader(['title' => 'Report'])
            ->page(Page::a4()->withContent($content));
    }
}

// Usage
$report = (new ReportBuilder())
    ->addSection('Introduction', '...')
    ->addSection('Analysis', '...')
    ->build();

$report->save('report.pdf');
```

### Data-Driven PDF Generation

Generate PDFs from data structures:

```php
$data = [
    'title' => 'Sales Report',
    'date' => '2024-01-15',
    'items' => [
        ['name' => 'Product A', 'sales' => 100],
        ['name' => 'Product B', 'sales' => 200],
    ],
];

$content = Column::make()
    ->addChildren([
        Heading::h1($data['title']),
        Text::make('Date: ' . $data['date']),
    ]);

foreach ($data['items'] as $item) {
    $content = $content->addChildren([
        Text::make($item['name'] . ': ' . $item['sales'] . ' units'),
    ]);
}

Pdf::make()->page(Page::a4()->withContent($content))->save('sales.pdf');
```

## Best Practices

### 1. Use Immutable Patterns

Always use the immutable pattern correctly:

```php
// Good
$page = Page::a4()->withContent($content);

// Avoid - don't try to modify after creation
$page->setContent($content); // This won't work
```

### 2. Chain Style Methods

Chain style methods for cleaner code:

```php
$style = Style::make()
    ->withPadding(20.0)
    ->withMargin(10.0)
    ->withColor(Color::hex('#333333'))
    ->withFontSize(14.0);
```

### 3. Use Helper Functions

Create helper functions for common patterns:

```php
function createPage(Node $content): Page
{
    return Page::a4()->withContent($content);
}

function createColumn(array $children): Column
{
    return Column::make()->addChildren($children);
}
```

### 4. Template Organization

Organize templates in a dedicated directory:

```
templates/
├── partials/
│   ├── header.folio
│   └── footer.folio
├── pages/
│   ├── invoice.folio
│   └── report.folio
└── components/
    ├── table.folio
    └── chart.folio
```

### 5. Error Handling

Handle errors gracefully:

```php
try {
    $pdf = $compiler->renderFile('template.folio', $data);
    $pdf->save('output.pdf');
} catch (\RuntimeException $e) {
    error_log('PDF generation failed: ' . $e->getMessage());
    // Handle error appropriately
}
```

### 6. Template Caching

Use template caching in production:

```php
$compiler = new PhpTemplateCompiler('/path/to/cache');
$compiler->setBaseDir('/path/to/templates');

// First call compiles and caches
$pdf1 = $compiler->renderFile('template.folio', $data);

// Subsequent calls use cache
$pdf2 = $compiler->renderFile('template.folio', $data2);
```

### 7. Strict Mode

Enable strict mode for production:

```php
$compiler = new PhpTemplateCompiler();
$compiler->setStrict(true); // Catches undefined variables
```

### 8. Memory Management

For large documents, consider memory usage:

```php
// Clear runtime cache between documents
$compiler->clearRuntimeCache();

// Process documents in batches
foreach ($documents as $doc) {
    $pdf = generatePdf($doc);
    $pdf->save($doc['filename']);
    unset($pdf); // Free memory
}
```

## Performance Tips

1. **Use Template Caching**: Templates are compiled once and cached
2. **Batch Operations**: Generate multiple PDFs in a single process
3. **Optimize Images**: Compress images before adding to PDFs
4. **Reuse Components**: Create reusable style objects
5. **Profile**: Use profiling tools to identify bottlenecks

## Troubleshooting

### Template Not Found

Ensure the template path is correct and the base directory is set:

```php
$compiler->setBaseDir(__DIR__ . '/templates');
```

### Undefined Variable in Template

Enable strict mode to catch these errors:

```php
$compiler->setStrict(true);
```

### Cache Directory Permissions

Ensure the cache directory is writable:

```bash
mkdir -p /tmp/folio-pdf-cache
chmod 755 /tmp/folio-pdf-cache
```

## Additional Resources

- [API Reference](API.md)
- [Architecture](ARCHITECTURE.md)
- [Template Language Documentation](../website/template-language/overview.md)
- [GitHub Repository](https://github.com/mohammadraufzahed/folio)
