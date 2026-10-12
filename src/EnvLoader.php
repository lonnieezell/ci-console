<?php

declare(strict_types=1);

namespace CodeIgniter\Console;

final class EnvLoader
{
    /**
     * Loads environment variables from a .env file in the specified directory.
     * Sets them in the $_ENV superglobal.
     */
    public static function load(string $dir): void
    {
        $file = rtrim($dir, '/').'/.env';

        if (!is_file($file)) {
            return;
        }

        $lines = file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

        foreach ($lines as $line) {
            $line = trim($line);

            // Skip empty lines and comments
            if ($line === '' || str_starts_with($line, '#')) {
                continue;
            }

            // Parse key=value format
            $parts = explode('=', $line, 2);
            if (count($parts) !== 2) {
                continue;
            }

            [$key, $value] = $parts;
            $key = trim($key);
            $value = trim($value);

            // Skip empty keys
            if ($key === '') {
                continue;
            }

            $_ENV[$key] = self::unquoteValue($value);
        }
    }

    /**
     * Removes surrounding quotes from a value if present.
     * Handles both single and double quotes.
     */
    private static function unquoteValue(string $value): string
    {
        $value = trim($value);

        if (($value[0] ?? null) === '"' && ($value[-1] ?? null) === '"') {
            return substr($value, 1, -1);
        }

        if (($value[0] ?? null) === "'" && ($value[-1] ?? null) === "'") {
            return substr($value, 1, -1);
        }

        return $value;
    }
}
