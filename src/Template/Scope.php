<?php

declare(strict_types=1);

namespace Folio\Pdf\Template;

/**
 * Runtime scope for compiled templates.
 *
 * Replaces extract() with an explicit, safe variable lookup mechanism.
 * Supports nested scopes for foreach loops and if branches.
 *
 * In strict mode (enabled via strict=true), accessing an undefined
 * variable or property path throws a TemplateError instead of silently
 * returning an empty string.
 */
final class Scope
{
    /** @var array<string, mixed> */
    private array $data;

    /** @var array<string, mixed> */
    private array $locals;

    private ?self $parent;

    private bool $strict;

    /**
     * @param array<string, mixed> $data
     * @param array<string, mixed> $locals
     */
    public function __construct(array $data = [], array $locals = [], ?self $parent = null, ?bool $strict = null)
    {
        $this->data = $data;
        $this->locals = $locals;
        $this->parent = $parent;
        $this->strict = $strict ?? ($parent?->strict ?? false);
    }

    /**
     * Create a child scope with additional locals (e.g. foreach item).
     *
     * @param array<string, mixed> $locals
     */
    public function child(array $locals = []): self
    {
        return new self($this->data, $locals, $this);
    }

    /**
     * Get a variable by name, checking locals first, then data.
     */
    public function getVar(string $name): mixed
    {
        if (array_key_exists($name, $this->locals)) {
            return $this->locals[$name];
        }

        if (array_key_exists($name, $this->data)) {
            return $this->data[$name];
        }

        if ($this->parent !== null) {
            return $this->parent->getVar($name);
        }

        if ($this->strict) {
            throw new Error\TemplateError("Undefined variable: \${$name}");
        }

        return '';
    }

    /**
     * Resolve a dotted property path like company.name.
     *
     * @param array<int, string> $path
     */
    public function getPath(array $path): mixed
    {
        if ($path === []) {
            return '';
        }

        $value = $this->getVar($path[0]);

        $n = count($path);
        for ($i = 1; $i < $n; $i++) {
            $key = $path[$i];
            if (is_array($value)) {
                if (!array_key_exists($key, $value)) {
                    if ($this->strict) {
                        $fullPath = implode('.', $path);
                        throw new Error\TemplateError("Undefined property: {$fullPath}");
                    }
                    return '';
                }
                $value = $value[$key];
            } elseif (is_object($value)) {
                if (!property_exists($value, $key)) {
                    if ($this->strict) {
                        $fullPath = implode('.', $path);
                        throw new Error\TemplateError("Undefined property: {$fullPath}");
                    }
                    return '';
                }
                $value = $value->{$key};
            } else {
                if ($this->strict) {
                    $fullPath = implode('.', $path);
                    throw new Error\TemplateError("Cannot access property '{$key}' on non-object/non-array: {$fullPath}");
                }
                return '';
            }
        }

        return $value ?? '';
    }

    /**
     * Get the underlying data array.
     *
     * @return array<string, mixed>
     */
    public function getData(): array
    {
        return $this->data;
    }

    /**
     * Get the locals for get_defined_vars() compatibility.
     *
     * @return array<string, mixed>
     */
    public function getLocals(): array
    {
        return $this->locals;
    }
}
