# Folio PDF Formatter

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

### Use Tabs

```bash
php bin/format.php --file=template.folio --tabs
```

### Check if File Needs Formatting

```bash
php bin/format.php --file=template.folio --check
```

### Short Options

```bash
php bin/format.php -f template.folio -i 2 -t
```

## Programmatic Usage

### Basic Formatting

```php
use Folio\Pdf\Template\FormatterFactory;

$formatter = FormatterFactory::create();
$formatted = $formatter->format($template);
```

### Custom Configuration

```php
$formatter = FormatterFactory::withIndent(2, ' ');
$formatted = $formatter->format($template);
```

### Tab Indentation

```php
$formatter = FormatterFactory::withTabs(4);
$formatted = $formatter->format($template);
```

### File Formatting

```php
$formatter = FormatterFactory::create();
$formatter->formatFile('template.folio');
```

### Check if Formatting Needed

```php
$formatter = FormatterFactory::create();
if ($formatter->needsFormatting('template.folio')) {
    echo "File needs formatting\n";
}
```

### Format Only if Changed

```php
$formatter = FormatterFactory::create();
$changed = $formatter->formatIfChanged('template.folio');
if ($changed) {
    echo "File was formatted\n";
}
```

## Configuration

### Factory Methods

- `create()` - Default formatter (4 spaces)
- `withIndent(int $size, string $character)` - Custom indentation
- `withTabs(int $tabSize)` - Tab indentation
- `compact()` - Compact formatter (2 spaces)
- `noFinalNewline()` - No final newline
- `fromConfig(array $config)` - From configuration array

### Configuration Array

```php
$config = [
    'indentSize' => 4,
    'indentCharacter' => ' ',
    'insertFinalNewline' => true,
];

$formatter = FormatterFactory::fromConfig($config);
```

## Formatting Rules

### Indentation

- Default: 4 spaces
- Nested blocks increase indentation by one level
- Closing braces decrease indentation

### Block Formatting

```folio
// Before
page{column{heading"Title"}}

// After
page {
    column {
        heading "Title"
    }
}
```

### Control Structures

```folio
// Before
if condition{content}

// After
if condition {
    content
}
```

### Attributes

```folio
// Before
page(padding=20,margin=10)

// After
page (padding = 20, margin = 10)
```

### Comments

```folio
// Comments are preserved
// This is a comment
page { ... }
```

## Integration

### Git Pre-commit Hook

```bash
#!/bin/bash
# .git/hooks/pre-commit

php bin/format.php --check $(git diff --name-only --cached '*.folio')
if [ $? -ne 0 ]; then
    echo "Some files need formatting. Run: php bin/format.php --file=<file>"
    exit 1
fi
```

### Composer Script

Add to `composer.json`:

```json
{
  "scripts": {
    "format": "php bin/format.php --file=template.folio",
    "format:check": "php bin/format.php --file=template.folio --check"
  }
}
```

### CI/CD Integration

```yaml
# GitHub Actions
- name: Check formatting
  run: php bin/format.php --check template.folio
```

## Editor Integration

### VS Code

The VS Code extension includes built-in formatting. Configure in settings:

```json
{
  "folio-pdf.format.enable": true,
  "folio-pdf.format.indentSize": 4
}
```

### Vim/Neovim

```vim
" Format on save
autocmd BufWritePre *.folio :!php bin/format.php --file=%
```

### Emacs

```elisp
(add-hook 'before-save-hook
          (lambda ()
            (when (string= (file-name-extension buffer-file-name) "folio")
              (shell-command (concat "php bin/format.php --file=" (buffer-file-name))))))
```

## Performance

- Efficient string processing
- Minimal memory footprint
- Fast formatting for large files
- Batch processing support

## Troubleshooting

### File Not Found

Ensure the file path is correct:
```bash
php bin/format.php --file=/full/path/to/template.folio
```

### Permission Denied

Check file permissions:
```bash
chmod +w template.folio
```

### Encoding Issues

Ensure files are UTF-8 encoded:
```bash
file -i template.folio
```

## Best Practices

1. **Format on Save**: Configure editor to format on save
2. **Pre-commit Hooks**: Use Git hooks to enforce formatting
3. **CI Checks**: Add formatting checks to CI/CD pipeline
4. **Team Standards**: Agree on indentation and formatting rules
5. **Documentation**: Document any custom formatting rules

## Future Enhancements

- Configuration file support (.formatterrc)
- Auto-detection of project formatting style
- Format ranges (partial formatting)
- Diff formatting (show only changes)
- Format multiple files at once
- Integration with other formatters
