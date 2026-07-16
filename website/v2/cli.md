# CLI

The `folio` CLI provides a fast local workflow for rendering, compiling and
previewing templates.

## Commands

```bash
# Render a template to a PDF
folio render --template=invoice.folio --data='{"name":"Acme"}' --output=invoice.pdf

# Compile a template to PHP
folio compile --template=invoice.folio --output=invoice.php

# Start the dev server
folio serve --port=8080 --templates=./templates

# Clear the template cache
folio cache:clear
```

## Dev server

The dev server exposes `/render?template=<file>&data=<json>&v2=1` and streams a
PDF back to the browser. Use it for rapid iteration without writing PHP code.
