<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use Folio\Pdf\Benchmarks\DocumentBenchmark;
use Folio\Pdf\Benchmarks\MicroBenchmark;
use Folio\Pdf\Benchmarks\StressBenchmark;

$benchmarks = [
    new MicroBenchmark('micro'),
    new DocumentBenchmark('document'),
    new StressBenchmark(),
];

$results = [];

foreach ($benchmarks as $benchmark) {
    $iterations = $benchmark instanceof StressBenchmark ? 1 : 10;
    $stats = $benchmark->run($iterations);

    $results[$benchmark->name] = [
        'duration_ms' => round($stats['duration'] * 1000, 3),
        'memory_mb' => round($stats['memory'] / 1024 / 1024, 3),
        'iterations' => $stats['iterations'],
        'mean_ms' => round(($stats['duration'] / $stats['iterations']) * 1000, 3),
    ];
}

$output = json_encode($results, JSON_PRETTY_PRINT);
echo $output . "\n";

$outFile = $argv[1] ?? null;

if ($outFile !== null) {
    file_put_contents($outFile, $output);
}
