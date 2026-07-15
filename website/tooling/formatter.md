# Formatter

The Folio formatter normalizes `.folio` files so your templates stay consistent regardless of who edits them. It handles indentation, attribute spacing, and nested block layout.

## Features

- Configurable indentation size
- Tab or space indentation
- Consistent attribute formatting
- Check mode for CI
- Preserves comments

## CLI Usage

Format a single file:

```bash
php bin/format.php --file=template.folio
```

Format with two-space indentation:

```bash
php bin/format.php --file=template.folio --indent=2
```

Check whether a file needs formatting without writing to disk:

```bash
php bin/format.php --file=template.folio --check
```

Format every `.folio` file in a directory:

```bash
php bin/format.php --dir=templates
```

## CI Integration

Run the formatter in check mode as part of your test suite:

```bash
php bin/format.php --dir=templates --check
```

If any file is not formatted, the command exits with a non-zero status.

## VS Code Integration

The [VS Code extension](./vscode.md) formats `.folio` files on save and on demand with `Shift+Alt+F`.
