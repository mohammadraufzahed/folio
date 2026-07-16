<?php

declare(strict_types=1);

namespace Folio\Pdf\Cli;

use Folio\Pdf\Template\PhpTemplateCompiler;
use Folio\Pdf\Template\TemplateEngine;

final class FolioCli
{
    /**
     * @param array<int, string> $argv
     */
    public static function run(array $argv): int
    {
        $command = $argv[1] ?? '';

        return match ($command) {
            'render' => self::render(array_slice($argv, 2)),
            'compile' => self::compile(array_slice($argv, 2)),
            'serve' => self::serve(array_slice($argv, 2)),
            'cache:clear' => self::clearCache(array_slice($argv, 2)),
            default => self::help(),
        };
    }

    /**
     * @param array<int, string> $args
     */
    private static function render(array $args): int
    {
        $options = self::parseOptions($args);

        if (!isset($options['template'])) {
            echo "Usage: folio render --template=<path> [--data=<json>] [--output=<path>] [--v2]\n";
            return 1;
        }

        $template = (string) $options['template'];
        $data = [];

        if (isset($options['data'])) {
            $decoded = json_decode((string) $options['data'], true);

            if (!is_array($decoded)) {
                echo "Error: --data must be a valid JSON object\n";
                return 1;
            }

            $data = $decoded;
        }

        $engine = new TemplateEngine();

        if (isset($options['v2'])) {
            $engine->enableFolio2Syntax(dirname($template));
        }

        try {
            $pdf = $engine->renderFile($template, $data);
        } catch (\Throwable $e) {
            echo 'Error: ' . $e->getMessage() . "\n";
            return 1;
        }

        if (isset($options['output'])) {
            file_put_contents((string) $options['output'], $pdf);
            echo 'Rendered: ' . $options['output'] . "\n";
        } else {
            echo $pdf;
        }

        return 0;
    }

    /**
     * @param array<int, string> $args
     */
    private static function compile(array $args): int
    {
        $options = self::parseOptions($args);

        if (!isset($options['template'])) {
            echo "Usage: folio compile --template=<path> [--output=<php>]\n";
            return 1;
        }

        $template = (string) $options['template'];
        $compiler = new PhpTemplateCompiler();

        try {
            $php = $compiler->compileFile($template);
        } catch (\Throwable $e) {
            echo 'Error: ' . $e->getMessage() . "\n";
            return 1;
        }

        if (isset($options['output'])) {
            file_put_contents((string) $options['output'], $php);
            echo 'Compiled: ' . $options['output'] . "\n";
        } else {
            echo $php;
        }

        return 0;
    }

    /**
     * @param array<int, string> $args
     */
    private static function serve(array $args): int
    {
        $options = self::parseOptions($args);
        $port = (int) ($options['port'] ?? 8080);
        $templatesDir = (string) ($options['templates'] ?? getcwd());
        $router = __DIR__ . '/router.php';

        echo "Starting Folio dev server on http://localhost:{$port}\n";
        echo "Templates directory: {$templatesDir}\n";

        $command = sprintf(
            'php -S localhost:%d -t %s %s',
            $port,
            escapeshellarg($templatesDir),
            escapeshellarg($router),
        );

        passthru($command, $exitCode);

        return $exitCode;
    }

    /**
     * @param array<int, string> $args
     */
    private static function clearCache(array $args): int
    {
        $options = self::parseOptions($args);
        $cacheDir = (string) ($options['cache'] ?? sys_get_temp_dir() . '/folio-pdf-cache');

        if (!is_dir($cacheDir)) {
            echo "Cache directory does not exist: {$cacheDir}\n";
            return 0;
        }

        $files = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($cacheDir, \FilesystemIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::CHILD_FIRST,
        );

        foreach ($files as $file) {
            if ($file->isDir()) {
                rmdir((string) $file->getPathname());
            } else {
                unlink((string) $file->getPathname());
            }
        }

        echo "Cleared cache: {$cacheDir}\n";

        return 0;
    }

    private static function help(): int
    {
        echo "Folio CLI\n";
        echo "\n";
        echo "Commands:\n";
        echo "  render --template=<path> [--data=<json>] [--output=<path>] [--v2]\n";
        echo "  compile --template=<path> [--output=<php>]\n";
        echo "  serve [--port=<number>] [--templates=<dir>]\n";
        echo "  cache:clear [--cache=<dir>]\n";

        return 1;
    }

    /**
     * @param array<int, string> $args
     * @return array<string, mixed>
     */
    private static function parseOptions(array $args): array
    {
        $options = [];
        $count = count($args);

        for ($i = 0; $i < $count; $i++) {
            $arg = $args[$i];

            if (str_starts_with($arg, '--')) {
                $option = substr($arg, 2);

                if (str_contains($option, '=')) {
                    [$key, $value] = explode('=', $option, 2);
                    $options[$key] = $value;
                } else {
                    $options[$option] = true;

                    if ($i + 1 < $count && !str_starts_with($args[$i + 1], '-')) {
                        $options[$option] = $args[++$i];
                    }
                }
            }
        }

        return $options;
    }
}
