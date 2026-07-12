#!/usr/bin/env php
<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use Folio\Pdf\Template\FormatterFactory;

$options = getopt('f:i:t', ['file:', 'indent:', 'tabs', 'check']);

if (empty($options['file']) && empty($options['f'])) {
    echo "Usage: php bin/format.php --file=<path> [--indent=<size>] [--tabs] [--check]\n";
    echo "       php bin/format.php -f <path> [-i <size>] [-t] [--check]\n\n";
    echo "Options:\n";
    echo "  -f, --file=<path>    File to format\n";
    echo "  -i, --indent=<size>  Indentation size (default: 4)\n";
    echo "  -t, --tabs          Use tabs instead of spaces\n";
    echo "  --check             Check if file needs formatting (exit code 1 if yes)\n";
    exit(1);
}

$filePath = $options['file'] ?? $options['f'];
$indentSize = (int)($options['indent'] ?? $options['i'] ?? 4);
$useTabs = isset($options['tabs']) || isset($options['t']);
$checkOnly = isset($options['check']);

if (!file_exists($filePath)) {
    echo "Error: File not found: $filePath\n";
    exit(1);
}

// Create formatter
if ($useTabs) {
    $formatter = FormatterFactory::withTabs($indentSize);
} else {
    $formatter = FormatterFactory::withIndent($indentSize);
}

// Format or check
if ($checkOnly) {
    if ($formatter->needsFormatting($filePath)) {
        echo "File needs formatting: $filePath\n";
        exit(1);
    } else {
        echo "File is properly formatted: $filePath\n";
        exit(0);
    }
} else {
    $formatted = $formatter->formatFile($filePath);
    echo "Formatted: $filePath\n";
    exit(0);
}
