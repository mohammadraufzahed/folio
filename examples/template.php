<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

$engine = new \Folio\Pdf\Template\TemplateEngine();

$template = <<<'FOLIO'
page(background="#ffffff") {
  column(width="100%", background="#ffffff") {
    column(width="100%", background="#0f172a", padding=40) {
      heading(color="#ffffff", fontSize=28) title
    }
    column(width="100%", padding=48, gap=20) {
      text(color="#334155", fontSize=11, lineHeight=1.6) content
    }
  }
}
FOLIO;

$pdf = $engine->render($template, ['title' => 'Inline Template', 'content' => 'Premium style from an inline template.']);

file_put_contents(__DIR__ . '/template.pdf', $pdf);

echo "Generated template.pdf\n";
