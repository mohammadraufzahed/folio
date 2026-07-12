<?php

declare(strict_types=1);

namespace Folio\Pdf\Template;

/** Runtime helpers for compiled templates. */
final class Runtime
{
    /**
     * @param array<string, mixed> $data
     * @param array<int, string> $path
     * @param array<string, mixed> $locals
     */
    public static function get(array $data, array $path, array $locals = []): mixed
    {
        if ($path === []) {
            return '';
        }

        $root = $path[0];
        $value = array_key_exists($root, $locals) ? $locals[$root] : ($data[$root] ?? null);

        $n = count($path);
        for ($i = 1; $i < $n; $i++) {
            $key = $path[$i];
            if (is_array($value)) {
                $value = $value[$key] ?? null;
            } elseif (is_object($value)) {
                $value = $value->{$key} ?? null;
            } else {
                return '';
            }
        }

        return $value ?? '';
    }
}
