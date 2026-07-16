<?php

declare(strict_types=1);

require_once __DIR__ . '/../../vendor/autoload.php';

use Folio\Pdf\Template\TemplateEngine;

$uri = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);

if ($uri === '/_folio/health') {
    http_response_code(200);
    echo json_encode(['status' => 'ok', 'time' => time()]);
    return true;
}

if ($uri !== '/render') {
    http_response_code(404);
    echo 'Not found. Use /render?template=<file>&data=<json>';
    return true;
}

$template = $_GET['template'] ?? '';
$data = [];

if (isset($_GET['data'])) {
    $decoded = json_decode((string) $_GET['data'], true);

    if (is_array($decoded)) {
        $data = $decoded;
    }
}

if ($template === '' || !is_file($template)) {
    http_response_code(400);
    echo 'Missing or invalid template parameter';
    return true;
}

$engine = new TemplateEngine();

try {
    $pdf = $engine->renderFile($template, $data);
} catch (\Throwable $e) {
    http_response_code(500);
    echo 'Render error: ' . $e->getMessage();
    return true;
}

header('Content-Type: application/pdf');
header('Content-Length: ' . strlen($pdf));
echo $pdf;

return true;
