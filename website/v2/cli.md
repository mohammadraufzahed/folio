# CLI

The `folio` CLI is a small PHP script in `bin/folio.php`. It is useful for
rendering and compiling templates locally and for starting a tiny dev server.

## Commands

```bash
php bin/folio.php render --template=invoice.folio --data='{"customer":"Acme"}' --output=invoice.pdf --v2
php bin/folio.php compile --template=invoice.folio --output=invoice.php
php bin/folio.php serve --port=8080 --templates=./templates
php bin/folio.php cache:clear
```

## render

Renders a `.folio` template to a PDF.

```bash
php bin/folio.php render \
  --template=examples/templates/invoice.folio \
  --data='{"customerName":"Acme","total":"$1,000.00"}' \
  --output=/tmp/invoice.pdf \
  --v2
```

Options:

- `--template` — path to the `.folio` file.
- `--data` — JSON object passed as template props.
- `--output` — destination PDF path. If omitted, the PDF bytes are written to
  stdout.
- `--v2` — enable the Folio 2.0 preprocessor (`@use`, `prop`, `foreach`,
  interpolation).

## compile

Compiles a template to the generated PHP without executing it. This is useful
for debugging the template compiler.

```bash
php bin/folio.php compile --template=examples/templates/invoice.folio --output=invoice.php
```

`compile` does not run the v2 preprocessor; it compiles the raw template.

## serve

Starts a PHP built-in server for rapid iteration.

```bash
php bin/folio.php serve --port=8080 --templates=./templates
```

The server exposes `/render?template=<file>&data=<json>&v2=1` and streams a
PDF back to the browser.

```bash
curl "http://localhost:8080/render?template=invoice.folio&data=%7B%22customer%22%3A%22Acme%22%7D&v2=1" -o invoice.pdf
```

## cache:clear

Folio caches compiled templates in `sys_get_temp_dir() . '/folio-pdf-cache'` by
default. Clear the cache with:

```bash
php bin/folio.php cache:clear
php bin/folio.php cache:clear --cache=/custom/cache/dir
```
