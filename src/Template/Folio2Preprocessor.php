<?php

declare(strict_types=1);

namespace Folio\Pdf\Template;

final class Folio2Preprocessor
{
    private ?string $baseDir;
    /** @var array<string, true> */
    private array $processing = [];

    public function __construct(?string $baseDir = null)
    {
        $this->baseDir = $baseDir;
    }

    public function process(string $template, ?string $path = null): string
    {
        $key = $path ?? '__inline__';

        if (isset($this->processing[$key])) {
            throw new \RuntimeException("Cyclic template dependency detected: {$key}");
        }

        $this->processing[$key] = true;

        try {
            $lines = preg_split('/\r\n|\n|\r/', $template);
            $result = [];

            foreach ($lines as $line) {
                $trimmed = trim($line);

                if ($trimmed === '') {
                    $result[] = $line;
                    continue;
                }

                if (str_starts_with($trimmed, '@use ')) {
                    $included = $this->processUse($trimmed);
                    $result[] = $included;
                    continue;
                }

                if (str_starts_with($trimmed, '@theme ')) {
                    $result[] = $line;
                    continue;
                }

                if (str_starts_with($trimmed, 'prop ')) {
                    $result[] = $this->processProp($trimmed);
                    continue;
                }

                $result[] = $line;
            }

            return implode("\n", $result);
        } finally {
            unset($this->processing[$key]);
        }
    }

    private function processUse(string $line): string
    {
        if (!preg_match('/^@use\s+"([^"]+)"\s*$/', $line, $matches)) {
            return $line;
        }

        $path = $matches[1];
        $resolved = $this->resolvePath($path);

        if ($resolved === null || !is_file($resolved)) {
            throw new \RuntimeException("Template include not found: {$path}");
        }

        $content = file_get_contents($resolved);

        if ($content === false) {
            throw new \RuntimeException("Unable to read template include: {$resolved}");
        }

        return $this->process($content, $resolved);
    }

    private function processProp(string $line): string
    {
        if (!preg_match('/^prop\s+(\w+)\s*:\s*[^=\s]+(?:\s*=\s*(.+))?$/', $line, $matches)) {
            return $line;
        }

        $name = $matches[1];
        $default = $matches[2] ?? '""';

        return "var {$name} = {$default}";
    }

    private function resolvePath(string $path): ?string
    {
        if (is_file($path)) {
            return $path;
        }

        if ($this->baseDir !== null) {
            $candidate = $this->baseDir . '/' . $path;

            if (is_file($candidate)) {
                return $candidate;
            }

            if (!str_ends_with($candidate, '.folio')) {
                $candidate .= '.folio';

                if (is_file($candidate)) {
                    return $candidate;
                }
            }
        }

        if (!str_ends_with($path, '.folio')) {
            $candidate = $path . '.folio';

            if (is_file($candidate)) {
                return $candidate;
            }
        }

        return null;
    }
}
