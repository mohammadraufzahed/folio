# Language Server

The Folio Language Server provides IDE features for `.folio` templates. It runs as a standalone PHP process and communicates over the Language Server Protocol.

## Features

- Syntax validation
- Autocomplete for elements, attributes, and variables
- Error diagnostics
- Hover information for built-in elements

## Running the Server

Start the server from the repository root:

```bash
php lsp/lsp.php
```

The server reads JSON-RPC messages from `stdin` and writes responses to `stdout`.

## Editor Configuration

### VS Code

The [VS Code extension](./vscode.md) starts the LSP automatically when you open a `.folio` file.

### Neovim / Vim

Configure a language server client to launch:

```lua
require('lspconfig').folio.setup {
  cmd = { 'php', 'lsp/lsp.php' },
  filetypes = { 'folio' },
  root_dir = require('lspconfig').util.root_pattern('.git'),
}
```

### Generic LSP Client

```json
{
  "command": "php",
  "args": ["/path/to/folio/lsp/lsp.php"],
  "filetypes": ["folio", "pdf-template"],
  "rootPatterns": [".git"]
}
```

## Logs

The server writes errors to `stderr`. In VS Code, open the Output panel and select the "Folio PDF LSP" channel.
