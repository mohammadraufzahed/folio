# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

## [2.0.3] - 2026-07-15

### Added
- `@use "path"` partial inlining is now fully wired in the v2 template engine.
- `@theme "name"` loads JSON design-token themes and applies named styles at runtime.
- `@style { ... }` parses CSS-like style blocks and applies them to the document.
- `class="..."` attribute support on elements for theme and stylesheet-based styling.
- New `FolioThemeRepository` and `FolioStyleParser` runtime adapters.
- New `examples/themed-invoice.php` and `examples/templates/themes/modern.json` demonstrating `@theme` and `@style`.

### Changed
- `TemplateEngine` no longer requires `enableFolio2Syntax()`; v2 preprocessing is always active.
- Updated v2 documentation (`template-language.md`, `styling.md`, `index.md`, `migration.md`) to remove "not wired" language.

## [2.0.2] - 2026-07-16

### Fixed
- VS Code extension now bundles the LSP server with esbuild, so language features work in the packaged VSIX.

## [2.0.1] - 2026-07-16

### Added
- TypeScript language server (`vscode-extension/src/server/`) replaces the PHP-based LSP.
- VS Code extension now bundles the LSP and no longer requires a local PHP binary.
- v2 documentation is now the primary site content, with v1 docs archived under `v1.x`.

## [2.0.0] - 2026-07-16

### Added
- Folio 2.0: ground-up redesign with onion/hexagonal architecture, immutable document model, layout tree, pagination, `Pdf1_7Renderer`, and `.folio` template language.
- `TemplateEngine` with v2 preprocessor supporting `prop`, `@use`, string interpolation, `if`/`else` and `foreach ... as`.
- `StyleEngine` with typed `ComputedStyle`, `BoxStyle`, `TextStyle`, `LayoutStyle` and `PaintStyle`.
- CLI commands `render`, `compile`, `serve` and `cache:clear`.
- Benchmarks, golden regression tests and expanded v2 documentation.

### Changed
- **Breaking:** removed the v1 `PdfFileWriter` and `Document::generate()` path; use the v2 `TemplateEngine` or PHP builder API.

## [1.1.3] - 2026-07-16

### Changed
- Patch release to test the updated release workflow that builds and attaches the VSIX.
- Fixed `Release` workflow to use `npm install` for the VS Code extension dependencies.

## [1.1.2] - 2026-07-16

### Changed
- Patch release to test the updated release workflow that builds and attaches the VSIX.

## [1.1.1] - 2026-07-16

### Added
- Reusable `Quality` workflow shared by CI and release pipelines.
- Concurrency controls to cancel stale CI runs and serialize releases.
- `version` field in `composer.json` for explicit release tracking.

### Changed
- CI and release workflows now call a single reusable quality job instead of duplicating setup steps.
- `composer validate` no longer uses `--strict` because the committed `version` field triggers an intentional Composer warning for VCS-published packages.

## [1.1.0] - 2026-07-16

### Added
- `.github/workflows/release.yml` for automated GitHub Releases and Packagist updates.
- `bin/format.php` registered as a Composer binary.
- Release documentation at `website/contributing/releases.md`.

## [1.0.0] - 2026-07-15

### Added
- LICENSE file (MIT License)
- CONTRIBUTING.md with contribution guidelines
- SECURITY.md with security policy and reporting procedures
- CODE_OF_CONDUCT.md with community guidelines
- Comprehensive documentation for open-source readiness

### Changed
- Fixed property declaration order in PhpTemplateCompiler (baseDir and partialDirs moved to top of class)

### Security
- Initial security policy and vulnerability reporting process

## [0.1.0] - 2024-XX-XX

### Added
- Initial release of Folio PDF
- PHP 8.3+ with strict types
- Composable document model with immutable nodes
- Custom template language with lexer, parser, and compiler
- Layout engine with flex and grid layouts
- Styling system with comprehensive style properties
- PDF generation with custom PDF writer
- Template compilation with disk and in-memory caching
- Page header and footer chrome with theming
- Table support with simple, nested, multi-header, and multi-level tables
- Pagination engine for automatic page breaking
- Language Server Protocol (LSP) support
- VS Code extension with syntax highlighting
- Tree-sitter grammar for the template language
- Formatter for the template language
- Comprehensive test suite with PHPUnit
- Static analysis with PHPStan
- Documentation website with VitePress

### Features
- Zero runtime dependencies
- PSR-4 autoloading
- Fluent builder API
- Strongly typed styling system
- Template partials and includes
- Conditional rendering (if/else)
- Loops (foreach with empty clauses)
- Property access and expressions
- Strict mode for template compilation
- Multiple page sizes (A4, A3, Letter, custom)
- Landscape orientation support
- Custom fonts and colors
- Borders, shadows, and backgrounds
- Text alignment and spacing
- Flex layout with grow/shrink
- Grid layout system

### Documentation
- Getting started guide
- API reference
- Template language documentation
- Tooling documentation
- Example templates and PHP code

[Unreleased]: https://github.com/mohammadraufzahed/folio/compare/v1.1.3...HEAD
[1.1.3]: https://github.com/mohammadraufzahed/folio/compare/v1.1.2...v1.1.3
[1.1.2]: https://github.com/mohammadraufzahed/folio/compare/v1.1.1...v1.1.2
[1.1.1]: https://github.com/mohammadraufzahed/folio/compare/v1.1.0...v1.1.1
[1.1.0]: https://github.com/mohammadraufzahed/folio/compare/v1.0.0...v1.1.0
[1.0.0]: https://github.com/mohammadraufzahed/folio/compare/v0.1.0...v1.0.0
[0.1.0]: https://github.com/mohammadraufzahed/folio/releases/tag/v0.1.0
