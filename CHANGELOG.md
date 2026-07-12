# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

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

[Unreleased]: https://github.com/folio/pdf/compare/v0.1.0...HEAD
[0.1.0]: https://github.com/folio/pdf/releases/tag/v0.1.0
