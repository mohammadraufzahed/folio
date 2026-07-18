# Examples

Folio ships with runnable examples in the `examples/` directory. Each one
demonstrates a real-world document and is a good starting point for your own
templates.

## Invoice

A two-column invoice with a dark header, an itemised table and a totals card.

```folio
prop customerName = ""
prop customerEmail = ""
prop invoiceNumber = ""
prop invoiceDate = ""
prop items = ""
prop total = "$0.00"

page(background="#ffffff") {
    column(width="100%", background="#ffffff") {
        column(width="100%", background="#0f172a", padding=40) {
            row(gap=24) {
                column(grow=1, gap=4) {
                    heading(color="#ffffff", fontSize=28) "Acme Corporation"
                    text(color="#94a3b8", fontSize=10) "123 Business Street, New York, NY 10001"
                }
                column(gap=4, align="right") {
                    text(color="#94a3b8", fontSize=9, fontWeight="bold") "INVOICE"
                    heading(color="#ffffff", fontSize=24, align="right") invoiceNumber
                }
            }
        }
        column(width="100%", padding=48, gap=28) {
            row(gap=48) {
                column(grow=1, gap=6) {
                    heading(color="#0f172a", fontSize=14) "Bill To"
                    text(color="#334155", fontSize=11) customerName
                    text(color="#64748b", fontSize=10) customerEmail
                }
                column(grow=1, gap=6) {
                    heading(color="#0f172a", fontSize=14) "Invoice Details"
                    text(color="#334155", fontSize=11) "Date: {invoiceDate}"
                    text(color="#334155", fontSize=11) "Terms: Net 30"
                }
            }

            heading(color="#0f172a", fontSize=16) "Items"
            table(padding=12, background="#f8fafc", width="100%") {
                header(background="#0f172a", color="#ffffff", fontSize=10, fontWeight="bold") {
                    th "Description"
                    th(align="right") "Quantity"
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
        }
    }
}
```

The matching PHP script passes the data array and writes `invoice.pdf`:

```php
$engine = new \Folio\Pdf\Template\TemplateEngine();
$pdf = $engine->renderFile('invoice.folio', [
        'customerName'  => 'Alice Johnson',
        'customerEmail' => 'alice@example.com',
        'invoiceNumber' => 'INV-001',
        'invoiceDate'   => date('Y-m-d'),
        'items' => [
            ['name' => 'Consulting', 'quantity' => '2', 'price' => '$500.00', 'total' => '$1,000.00'],
        ],
        'total' => '$1,000.00',
    ]);
file_put_contents(__DIR__ . '/invoice.pdf', $pdf);
```

## Certificate

A classic certificate with a dark background, a white content card and signature
lines.

```folio
prop recipient = ""
prop course = ""
prop date = ""
prop issuer = ""

page(background="#0f172a") {
    column(width="100%", padding=80, background="#0f172a") {
        column(width="100%", background="#ffffff", padding=64, gap=24, align="center") {
            text(color="#94a3b8", fontSize=11, fontWeight="bold", letterSpacing=2) "OFFICIAL CERTIFICATE"
            heading(color="#0f172a", fontSize=34) "Certificate of Completion"
            text(color="#64748b", fontSize=13) "This certifies that"
            heading(color="#2563eb", fontSize=28) recipient
            text(color="#64748b", fontSize=13) "has successfully completed the course"
            heading(color="#0f172a", fontSize=22) course

            text(color="#94a3b8", fontSize=10) "Issued on {date}"
            text(color="#94a3b8", fontSize=10) "Issued by {issuer}"

            row(gap=40, width="100%") {
                column(grow=1, gap=4) {
                    text(color="#94a3b8", fontSize=9, align="center") "____________________"
                    text(color="#64748b", fontSize=9, align="center") "Director"
                }
                column(grow=1, gap=4) {
                    text(color="#94a3b8", fontSize=9, align="center") "____________________"
                    text(color="#64748b", fontSize=9, align="center") "Instructor"
                }
            }
        }
    }
}
```

## Shipping label

A 4×6 inch shipping label that uses string interpolation to format the city,
state and zip on one line.

```folio
prop from = ""
prop to = ""
prop tracking = ""
prop weight = ""

page(size="288 x 432", background="#ffffff") {
    column(width="100%", padding=16, gap=12) {
        column(width="100%", background="#0f172a", padding=14, gap=4) {
            text(color="#94a3b8", fontSize=8, fontWeight="bold") "SHIP TO"
            heading(color="#ffffff", fontSize=16) to.name
        }
        text(color="#334155", fontSize=9, fontWeight="bold") to.address1
        text(color="#334155", fontSize=9) "{to.city}, {to.state} {to.zip}"
        text(color="#64748b", fontSize=8) to.country

        column(width="100%", padding=12, background="#f8fafc", gap=4) {
            text(color="#64748b", fontSize=7, fontWeight="bold") "FROM"
            text(color="#334155", fontSize=8, fontWeight="bold") from.name
            text(color="#334155", fontSize=8) from.address1
            text(color="#334155", fontSize=8) "{from.city}, {from.state} {from.zip}"
        }

        table(padding=10, background="#f8fafc", width="100%") {
            tr(background="#ffffff", fontSize=9) {
                td(fontWeight="bold") "Tracking"
                td(align="right") tracking
            }
            tr(background="#ffffff", fontSize=9) {
                td(fontWeight="bold") "Weight"
                td(align="right") weight
            }
            tr(background="#ffffff", fontSize=9) {
                td(fontWeight="bold") "Service"
                td(align="right") "Priority"
            }
        }
    }
}
```

## Resume

A multi-section resume using alternating card backgrounds and a two-column
experience list.

```folio
prop name = ""
prop title = ""
prop summary = ""
prop experiences = ""

page(background="#ffffff") {
    column(width="100%", background="#ffffff") {
        column(width="100%", background="#0f172a", padding=40) {
            heading(color="#ffffff", fontSize=28) name
            text(color="#60a5fa", fontSize=12) title
        }
        column(width="100%", padding=40, gap=32) {
            column(gap=8) {
                heading(color="#0f172a", fontSize=14) "Summary"
                text(color="#334155", fontSize=10) summary
            }
            column(gap=12) {
                heading(color="#0f172a", fontSize=14) "Experience"
                foreach experiences as exp {
                    column(padding=16, background="#f8fafc", gap=6) {
                        heading(color="#0f172a", fontSize=11) exp.role
                        text(color="#64748b", fontSize=9) "{exp.company} — {exp.period}"
                        text(color="#334155", fontSize=9) exp.description
                    }
                }
            }
        }
    }
}
```

## Company report

The most complex example: a multi-page report built entirely in PHP that shows
tables, paginated sections and styled headers.

See `examples/company-report.php` for the full source. It uses the v2 PHP
builder API to compose multiple pages, each containing `Column`, `Row`,
`Table` and `Text` nodes, and writes `company-report.pdf`.

## Running the examples

From the repository root:

```bash
composer install
php examples/invoice.php
php examples/certificate.php
php examples/shipping-label.php
php examples/resume.php
php examples/company-report.php
```

Each script generates its PDF next to the source file.

## Premium themed examples

The `pro-` examples demonstrate `@theme`, `@style` and `@use` partials working
together. They share a single theme file (`examples/templates/themes/pro.json`)
that defines colors, spacing and font sizes, and a reusable header partial.

```bash
php examples/pro-invoice.php
php examples/pro-certificate.php
php examples/pro-receipt.php
```

Look inside `examples/templates/pro-*.folio` to see how `@theme "pro"`,
`@style { ... }` blocks and `class="brand"` keep the markup clean while the
design tokens live in one place.
