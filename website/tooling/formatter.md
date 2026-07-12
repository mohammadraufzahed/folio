# Formatter

A standalone formatter for the Folio PDF template language, providing consistent code formatting and style enforcement.

## Features

- **Consistent Indentation**: Configurable indentation size and character
- **Block Formatting**: Proper formatting of nested blocks
- **Comment Preservation**: Maintains comments in formatted output
- **Attribute Formatting**: Consistent attribute formatting
- **Control Structure Formatting**: Proper if/foreach formatting
- **Check Mode**: Verify if files need formatting without modifying them

## Installation

The formatter is included with the Folio PDF library.

## CLI Usage

### Format a File

```bash
php bin/format.php --file=template.folio
```

### Custom Indentation

```bash
php bin/format.php --file=template.folio --indent=2
```

### Check Mode

Check if a file needs formatting without modifying it:

```bash
php bin/format.php --file=template.folio --check
```

### Format Directory

Format all `.folio` files in a directory:

```bash
php bin/format.php --dir=templates
```

## VS Code Integration

The [VS Code extension](./vscode.md) includes formatter integration. Use `Shift+Alt+F` to format the current document.
