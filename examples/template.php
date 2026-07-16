<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use Folio\Pdf\Template\PhpTemplateCompiler;
use Folio\Pdf\Template\TemplateEngine;

$template = <<<'FOLIO'
page(background="#f1f5f9") {
  column(padding=48, gap=20, background="#ffffff") {
    heading(color="#1e3a8a", fontSize=24, fontWeight="bold") "Template Demo"
    text(color="#334155", fontSize=11) "Rendered from an inline Folio template."
  }
}
FOLIO;

$compiler = new PhpTemplateCompiler();
$phpCode = $compiler->compile($template);

echo "=== Generated PHP Code ===\n";
echo $phpCode . "\n";
echo "=== End Generated Code ===\n\n";

$engine = (new TemplateEngine())->enableFolio2Syntax();
file_put_contents(__DIR__ . '/template.pdf', $engine->render($template, []));

echo 'Template PDF saved: ' . __DIR__ . '/template.pdf' . "\n";
