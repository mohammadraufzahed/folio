<?php

declare(strict_types=1);

namespace Folio\Pdf\Benchmarks;

abstract class Benchmark
{
    public readonly string $name;

    public function __construct(string $name)
    {
        $this->name = $name;
    }

    /**
     * @return array{duration: float, memory: int, iterations: int}
     */
    public function run(int $iterations = 10): array
    {
        $this->warmup();

        $start = hrtime(true);
        $memoryBefore = memory_get_peak_usage(true);

        for ($i = 0; $i < $iterations; $i++) {
            $this->execute();
        }

        $duration = (hrtime(true) - $start) / 1_000_000_000.0;
        $memory = memory_get_peak_usage(true) - $memoryBefore;

        return [
            'duration' => $duration,
            'memory' => $memory,
            'iterations' => $iterations,
        ];
    }

    abstract protected function warmup(): void;

    abstract protected function execute(): void;
}
