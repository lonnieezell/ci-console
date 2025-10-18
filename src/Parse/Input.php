<?php

declare(strict_types=1);

namespace CodeIgniter\Console\Parse;

/**
 * Class Input
 *
 * Parses command line input into command, arguments, and options.
 * Handles quoted strings and the `--` token to stop option parsing.
 *
 * @package CodeIgniter\Console\Parse
 */
final class Input
{
    private ?string $command = null;
    private array $args = [];
    private array $options = [];   // ['option' => 'value', 'flag' => true]
    private array $errors = [];    // ['error message', ...]

    /**
     * Gather Input from argv array.
     *
     * Supports:
     * - Empty string values: --option=""
     * - Boolean-like strings: --flag=false (stored as boolean)
     * - Duplicate options: Last one wins, no warning
     * - Short flags: -v or -f value (single letter can have value)
     * - Multiple short flags: -vf (all treated as boolean flags, Unix-standard)
     */
    public static function fromArgv(array $argv): self
    {
        $self = new self();
        $tokens = Tokenizer::tokenize($argv, $self);

        if ($tokens === []) {
            return $self;
        }

        $self->command = ltrim($tokens[0] ?? '', '-');

        $i = 1;
        while ($i < count($tokens)) {
            $t = $tokens[$i];

            if (str_starts_with($t, '--')) {
                $eq = strpos($t, '=');

                if ($eq !== false) {
                    $name = substr($t, 2, $eq-2);
                    // Capture error: empty option name
                    if ($name === '') {
                        $self->errors[] = "Invalid option: option name cannot be empty (got '--=')";
                        $i++;
                        continue;
                    }
                    $val  = substr($t, $eq+1);
                    $self->options[$name] = self::castBooleanLike($val);
                } else {
                    $name = substr($t, 2);
                    // next token as value if next doesn’t start with -
                    if (isset($tokens[$i+1]) && !str_starts_with($tokens[$i+1], '-')) {
                        $val = $tokens[++$i];
                        $self->options[$name] = self::castBooleanLike($val);
                    } else {
                        $self->options[$name] = true;
                    }
                }
            } elseif (str_starts_with($t, '-')) {
                // short flags like -vf or -o value
                $letters = substr($t, 1);

                // Capture error: lone dash with no flag
                if ($letters === '') {
                    $self->errors[] = "Invalid option: lone dash '-' with no flag";
                    $i++;
                    continue;
                }

                // Unix-standard: multiple letters are all boolean flags
                if (strlen($letters) > 1) {
                    foreach (str_split($letters) as $c) {
                        $self->options[$c] = true;
                    }
                } else {
                    // Single letter: can have a value
                    if (isset($tokens[$i + 1]) && !str_starts_with($tokens[$i + 1], '-')) {
                        $val = $tokens[++$i];
                        $self->options[$letters] = self::castBooleanLike($val);
                    } else {
                        $self->options[$letters] = true;
                    }
                }
            } else {
                $self->args[] = $t;
            }

            $i++;
        }
        return $self;
    }

    /**
     * Get the command name, or null if none.
     */
    public function command(): ?string
    {
        return $this->command;
    }

    /**
     * Get a single argument by index, with optional default value.
     */
    public function arg(int $index, ?string $default=null): ?string
    {
        return $this->args[$index] ?? $default;
    }

    /**
     * Get all arguments.
     */
    public function args(): array
    {
        return $this->args;
    }

    /**
     * Check if an option or flag exists.
     */
    public function has(string $option): bool
    {
        return array_key_exists($option, $this->options);
    }

    /**
     * Get a single option value, with optional default value.
     *
     * @return mixed
     */
    public function option(string $option, $default=null): mixed
    {
        return $this->options[$option] ?? $default;
    }

    /**
     * Get all options.
     */
    public function options(): array
    {
        return $this->options;
    }

    /**
     * Casts boolean-like strings to booleans, otherwise returns original value.
     * Recognizes: true, false, 1, 0, yes, no (case-insensitive)
     *
     * @param mixed $val
     * @return mixed
     */
    private static function castBooleanLike($val): mixed
    {
        if (!is_string($val)) {
            return $val;
        }
        $lower = strtolower($val);
        return match ($lower) {
            'true', '1', 'yes' => true,
            'false', '0', 'no' => false,
            default => $val,
        };
    }

    /**
     * Add a parsing error message.
     */
    public function addError(string $error): void
    {
        $this->errors[] = $error;
    }

    /**
     * Check if there are any parsing errors.
     */
    public function hasErrors(): bool
    {
        return count($this->errors) > 0;
    }

    /**
     * Get all parsing error messages.
     */
    public function errors(): array
    {
        return $this->errors;
    }

    /**
     * Get the first error message, or null if none.
     */
    public function firstError(): ?string
    {
        return $this->errors[0] ?? null;
    }

    /**
     * Clear all parsing errors.
     */
    public function clearErrors(): void
    {
        $this->errors = [];
    }
}
