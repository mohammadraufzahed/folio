# Installation

Folio requires PHP 8.3 or later and Composer. It has no runtime dependencies beyond PHP itself.

## Install via Composer

```bash
composer require mohammadraufzahed/folio
```

Install a specific release:

```bash
composer require mohammadraufzahed/folio:^1.0
```

Composer pulls the package from [Packagist](https://packagist.org/packages/mohammadraufzahed/folio). Source archives and release notes are also available on [GitHub Releases](https://github.com/mohammadraufzahed/folio/releases).

See the [release process](../contributing/releases.md) for how versions are tagged and published.

## Requirements

- PHP >= 8.3
- `mbstring` extension recommended for proper text metrics
- Composer 2.x

No additional extensions, libraries, or binaries are required.

## Development Dependencies

When contributing or running the test suite, install dev dependencies:

```bash
composer install
```

Then verify the installation:

```bash
composer test      # PHPUnit
composer analyze   # PHPStan
composer cs-check  # PHP-CS-Fixer dry run
```

## Optional Tooling

For the best editing experience, install the companion tools:

- [VS Code Extension](../tooling/vscode.md) — syntax highlighting, formatting, and LSP support
- [Formatter](../tooling/formatter.md) — standalone CLI formatter for `.folio` files
- [Language Server](../tooling/lsp.md) — autocomplete, diagnostics, and hover information
