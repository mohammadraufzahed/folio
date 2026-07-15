<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use Folio\Pdf\Template\PhpTemplateCompiler;

$template = 'page { column { heading "Template Demo" text "Generated from template" } }';

$compiler = new PhpTemplateCompiler();
$phpCode = $compiler->compile($template);

echo "=== Generated PHP Code ===\n";
echo $phpCode . "\n";
echo "=== End Generated Code ===\n\n";

$cachePath = $compiler->getCachePath($template);
file_put_contents($cachePath, $phpCode);

echo "Compiled template saved to: $cachePath\n";
