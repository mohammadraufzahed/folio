# Benchmarks

Performance is tracked on every release. The benchmark suite is pure PHP and
measures wall time and peak memory.

```bash
composer benchmark
```

## Scenarios

| Benchmark | Description |
|-----------|-------------|
| `micro`   | A minimal document with one text node. |
| `document`| A 50-section document with headings and paragraphs. |
| `stress`  | A 1,000-row table to exercise layout and rendering. |

Results are written to `benchmarks.json` and attached to each GitHub Release.
