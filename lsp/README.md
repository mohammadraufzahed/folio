# Folio PDF Language Server Protocol (LSP)

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

### Neovim Integration

Using `nvim-lspconfig`:

```lua
require'lspconfig'.folio_pdf.setup {
  cmd = {'php', '/path/to/folio/lsp/lsp.php'},
  filetypes = {'folio', 'pdf-template'},
}
```

### Vim Integration

Using `coc.nvim`:

```vim
" coc-settings.json
{
  "languageserver": {
    "folio-pdf": {
      "command": "php",
      "args": ["/path/to/folio/lsp/lsp.php"],
      "filetypes": ["folio", "pdf-template"]
    }
  }
}
```

## Capabilities

### Text Document Sync

- Full document synchronization
- Incremental updates
- Diagnostic publishing

### Completion

- Element names (page, column, row, text, heading)
- Control structures (if, else, foreach)
- Directives (@header, @footer, @import)
- Context-aware suggestions

### Diagnostics

- Syntax errors
- Unknown tokens
- Template compilation errors

### Hover

- Documentation for all elements
- Usage examples
- Parameter information

## Development

### Testing the LSP

```bash
# Start the server
php lsp/lsp.php

# Send a test request (from another terminal)
echo '{"jsonrpc":"2.0","id":1,"method":"initialize","params":{}}' | php lsp/lsp.php
```

### Adding New Features

1. Add the handler method in `Server.php`
2. Update the `handleRequest` match statement
3. Add corresponding capabilities in the `initialize` response
4. Update documentation

## Protocol Support

The server implements the following LSP methods:

- `initialize` - Server initialization
- `initialized` - Notification that initialization is complete
- `shutdown` - Graceful shutdown
- `textDocument/didOpen` - Document opened
- `textDocument/didChange` - Document changed
- `textDocument/completion` - Code completion
- `textDocument/diagnostics` - Diagnostics
- `textDocument/hover` - Hover information

## Debugging

Enable logging by checking the error log:

```bash
tail -f /var/log/php-error.log
```

Or run with verbose output:

```bash
php lsp/lsp.php 2>&1 | tee lsp.log
```

## Performance

- Incremental parsing for large files
- Cached compilation results
- Efficient tokenization
- Minimal memory footprint

## Future Enhancements

- Go to definition
- Find references
- Rename symbols
- Code formatting
- Document symbols
- Workspace symbols
- Code actions
