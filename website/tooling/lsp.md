# Language Server Protocol (LSP)

A Language Server Protocol implementation for the Folio PDF template language, providing IDE features like autocomplete, diagnostics, and hover information.

## Features

- **Syntax Validation**: Real-time syntax checking
- **Autocomplete**: Intelligent code completion for elements, directives, and keywords
- **Hover Information**: Documentation on hover
- **Diagnostics**: Error and warning reporting
- **LSP Compliant**: Full LSP 3.0 support

## Installation

The LSP server is included with the Folio PDF library.

## Usage

### Standalone Server

```bash
php lsp/lsp.php
```

### VS Code Integration

The [VS Code extension](./vscode.md) includes LSP integration.

### Manual Configuration

Add to your `settings.json`:

```json
{
  "languageserver": {
    "folio-pdf": {
      "command": "php",
      "args": ["/path/to/folio/lsp/lsp.php"],
      "filetypes": ["folio", "pdf-template"],
      "rootPatterns": [".git"],
      "settings": {}
    }
  }
}
```

## Features

### Autocomplete

- Element names (page, column, row, text, heading)
- Directives (@header, @footer, @import)
- Keywords (var, prop, if, foreach)
- Variable names from scope

### Diagnostics

- Syntax errors
- Unknown elements
- Missing required attributes
- Type mismatches (where applicable)

### Hover

- Element documentation
- Directive descriptions
- Attribute information
