# Install Folio PDF VS Code Extension

## Quick install (from this repo)

```bash
cd vscode-extension
npm install
npm run compile
npx vsce package --allow-missing-repository
code --install-extension folio-pdf-0.1.1.vsix --force
```

Then **reload VS Code / Cursor / Windsurf** (`Developer: Reload Window`).

## Verify

1. Open any `*.folio` file — status bar language should show **Folio PDF**
2. Command Palette → `Folio PDF: Format Document`
3. Command Palette → `Folio PDF: Restart Language Server`
4. Output panel → channel **Folio PDF LSP** should show the server running

## Format Document

- Command: `Folio PDF: Format Document` (`folio-pdf.formatDocument`)
- Or standard: **Format Document** (`Shift+Alt+F` / `Shift+Option+F`)
- Default formatter is set in workspace `.vscode/settings.json`

## LSP

The extension starts:

```bash
php <workspace>/lsp/lsp.php
```

Requirements:
- PHP 8.3+ on PATH (or set `folio-pdf.lsp.phpPath`)
- Workspace opened at the **folio** repo root (so `lsp/lsp.php` and `vendor/` resolve)

Optional settings:

```json
{
  "folio-pdf.lsp.enable": true,
  "folio-pdf.lsp.phpPath": "php",
  "folio-pdf.lsp.serverPath": "/absolute/path/to/folio/lsp/lsp.php"
}
```

## Troubleshooting

| Symptom | Fix |
|--------|-----|
| `formatDocument not found` | Reinstall VSIX + Reload Window |
| Language is Plain Text | Check `files.associations` for `*.folio` → `folio` |
| LSP not starting | Open **Folio PDF LSP** output; ensure `php lsp/lsp.php` works |
| Wrong PHP | Set `folio-pdf.lsp.phpPath` |
