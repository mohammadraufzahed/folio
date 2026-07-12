<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Folio\Pdf\Template\PhpTemplateCompiler;

// Example template compilation
$template = 'page { column { heading "Template Demo" text "Generated from template" } }';

$compiler = new PhpTemplateCompiler();
$phpCode = $compiler->compile($template);

echo "=== Generated PHP Code ===\n";
echo $phpCode . "\n";
echo "=== End Generated Code ===\n\n";

// Save compiled template
$cachePath = $compiler->getCachePath($template);
file_put_contents($cachePath, $phpCode);

echo "Compiled template saved to: $cachePath\n";

// To use the compiled template, you would:
// $pdf = require $cachePath;
// $pdf->save('output.pdf');
