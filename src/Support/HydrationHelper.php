<?php

declare(strict_types=1);

namespace Medzuch\DhlExpress\Support;

/**
 * Defensive helpers for hydrating raw decoded JSON into typed PHP values.
 *
 * DHL responses sometimes omit fields, return them with the wrong primitive
 * type, or return numeric values as strings. Each helper here takes the raw
 * decoded array and a key, and returns either a typed value or a safe
 * default (empty string / null) — never throws.
 *
 * Static utility class. The same exception to the "no static methods" rule
 * applies as to the planned `Support\Assert` helper described in §7 of
 * `PROJECT_PLAN.md`: these methods have no behaviour worth mocking; they are
 * pure functions over decoded JSON.
 */
final class HydrationHelper
{
    private function __construct()
    {
    }

    /**
     * @param array<string, mixed> $source
     */
    public static function stringField(array $source, string $key): string
    {
        $value = $source[$key] ?? null;

        return is_string($value) ? $value : '';
    }

    /**
     * @param array<string, mixed> $source
     */
    public static function nullableStringField(array $source, string $key): ?string
    {
        $value = $source[$key] ?? null;

        return is_string($value) ? $value : null;
    }

    /**
     * @param array<string, mixed> $source
     */
    public static function nullableBoolField(array $source, string $key): ?bool
    {
        $value = $source[$key] ?? null;

        return is_bool($value) ? $value : null;
    }

    /**
     * Coerces int, float, and numeric strings to float. Non-numeric values yield null.
     *
     * @param array<string, mixed> $source
     */
    public static function floatField(array $source, string $key): ?float
    {
        $value = $source[$key] ?? null;

        if (is_int($value) || is_float($value)) {
            return (float) $value;
        }
        if (is_string($value) && is_numeric($value)) {
            return (float) $value;
        }

        return null;
    }

    /**
     * Coerces int, float (truncated), and numeric strings to int. Non-numeric values yield null.
     *
     * @param array<string, mixed> $source
     */
    public static function intField(array $source, string $key): ?int
    {
        $value = $source[$key] ?? null;

        if (is_int($value)) {
            return $value;
        }
        if (is_float($value)) {
            return (int) $value;
        }
        if (is_string($value) && is_numeric($value)) {
            return (int) $value;
        }

        return null;
    }

    /**
     * Filters a raw value to a list of strings, dropping non-string entries.
     * Useful for arrays embedded inside response objects.
     *
     * @return list<string>
     */
    public static function stringList(mixed $raw): array
    {
        if (!is_array($raw)) {
            return [];
        }

        $items = [];
        foreach ($raw as $item) {
            if (is_string($item)) {
                $items[] = $item;
            }
        }

        return $items;
    }
}
