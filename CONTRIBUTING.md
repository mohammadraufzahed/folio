# Contributing to Folio PDF

Thank you for your interest in contributing to Folio PDF! This document provides guidelines and instructions for contributing to the project.

## Code of Conduct

By participating in this project, you agree to abide by our [Code of Conduct](CODE_OF_CONDUCT.md).

## Getting Started

### Prerequisites

- PHP 8.3 or higher
- Composer
- Git

### Installation

1. Fork the repository
2. Clone your fork:
   ```bash
   git clone https://github.com/your-username/folio.git
   cd folio
   ```
3. Install dependencies:
   ```bash
   composer install
   ```

### Running Tests

Run the test suite:
```bash
composer test
```

Run tests with coverage:
```bash
composer test -- --coverage-html coverage
```

### Static Analysis

Run PHPStan for static analysis:
```bash
composer analyze
```

## Development Workflow

### Branch Strategy

- `main` - Production-ready code
- Feature branches - `feature/your-feature-name`
- Bugfix branches - `fix/your-bugfix-name`

### Making Changes

1. Create a new branch from `main`
2. Make your changes following the coding standards
3. Add tests for new functionality
4. Ensure all tests pass
5. Run static analysis and fix any issues
6. Commit your changes with clear messages
7. Push to your fork
8. Create a pull request

### Commit Messages

Follow conventional commits format:
- `feat: add new feature`
- `fix: resolve bug in component`
- `docs: update documentation`
- `refactor: improve code structure`
- `test: add unit tests`
- `chore: update dependencies`

Example:
```
feat(table): add support for merged cells

Implement cell spanning functionality for tables to allow
cells to span multiple rows and columns.

- Add colspan and rowspan attributes to TableCell
- Update layout engine to handle merged cells
- Add tests for merged cell scenarios
```

## Coding Standards

### PHP Standards

- Follow PSR-12 coding style
- Use strict types (`declare(strict_types=1);`)
- Use type hints for all parameters and return types
- Prefer immutable data structures
- Use readonly properties where appropriate
- Add PHPDoc for public APIs

### Code Style

```php
<?php

declare(strict_types=1);

namespace Folio\Pdf\YourNamespace;

use Folio\Pdf\OtherNamespace\SomeClass;

/**
 * Brief description of what this class does.
 *
 * More detailed description if needed.
 */
final class YourClass
{
    private readonly string $property;

    public function __construct(string $property)
    {
        $this->property = $property;
    }

    /**
     * Brief description of the method.
     *
     * @param SomeType $param Description of parameter
     * @return ReturnType Description of return value
     */
    public function yourMethod(SomeType $param): ReturnType
    {
        // Implementation
    }
}
```

### Testing

- Write unit tests for all new functionality
- Aim for high test coverage
- Use descriptive test names
- Follow Arrange-Act-Assert pattern
- Mock external dependencies

Example:
```php
public function testItShouldReturnTrueWhenConditionIsMet(): void
{
    // Arrange
    $instance = new YourClass('test-value');
    
    // Act
    $result = $instance->yourMethod();
    
    // Assert
    $this->assertTrue($result);
}
```

## Project Structure

```
folio/
├── src/              # Source code
│   ├── Contracts/    # Interfaces
│   ├── Document/     # Document classes
│   ├── Layout/       # Layout engine
│   ├── Nodes/        # Document nodes
│   ├── Styling/      # Style system
│   ├── Template/     # Template compiler
│   └── ...
├── tests/            # Test files
├── examples/         # Example code
├── docs/             # Documentation
└── website/          # Documentation website
```

## Submitting Pull Requests

### PR Guidelines

1. **Small, focused PRs** - Keep changes small and focused on a single issue
2. **Clear description** - Explain what changes you made and why
3. **Tests included** - Add or update tests for your changes
4. **Documentation** - Update relevant documentation
5. **No merge conflicts** - Keep your branch up to date with main

### PR Template

When creating a PR, include:

- **Description**: What changes were made and why
- **Type**: Feature, Bugfix, Refactor, Documentation, etc.
- **Breaking changes**: List any breaking changes
- **Testing**: Describe how you tested the changes
- **Screenshots**: Add screenshots if applicable (UI changes)

### Review Process

1. Automated checks must pass (tests, linting)
2. At least one maintainer approval required
3. Address review feedback promptly
4. Squash commits if requested before merge

## Reporting Issues

When reporting bugs:

1. Search existing issues first
2. Use the issue template
3. Provide:
   - PHP version
   - Folio PDF version
   - Minimal reproduction code
   - Expected vs actual behavior
   - Error messages/stack traces

## Feature Requests

For feature requests:

1. Check if already requested
2. Describe the use case clearly
3. Explain why it's needed
4. Suggest an API if possible
5. Consider if it fits the project goals

## Documentation

- Update README for user-facing changes
- Add inline documentation for public APIs
- Update website documentation for significant features
- Include examples in documentation

## Release Process

Releases are managed by maintainers:
1. Update version in composer.json
2. Update CHANGELOG.md
3. Create git tag
4. Release on GitHub
5. Publish to Packagist

## Getting Help

- Check existing documentation
- Search GitHub issues
- Ask questions in GitHub Discussions
- Check the website: https://mohammadraufzahed.github.io/folio

## Recognition

Contributors are recognized in the CONTRIBUTORS.md file. All contributions are valued!

Thank you for contributing to Folio PDF!
