<?php

declare(strict_types=1);

namespace CodeIgniter\Console\Parse;

/**
 * Given the argv array, tokenizes it into arguments, and options.
 * Handles quoted strings and the `--` token to stop option parsing.
 * Casts boolean-like strings to booleans, otherwise returns original value.
 * Recognizes: true, false, 1, 0, yes, no (case-insensitive)
 */
class Tokenizer
{
    /**
     * Splits argv (minus script) into tokens: supports quotes and --
     * for stopping option parsing.
     */
    public static function tokenize(array $argv, ?Input $input = null): array
    {
        $argv = array_values($argv);
        array_shift($argv); // drop script

        $tokens = [];
        $current = '';
        $inQuote = false;
        $quoteChar = '';
        $stopParsing = false;

        foreach ($argv as $arg) {
            if ($stopParsing) {
                $tokens[] = $arg;
                continue;
            }

            if ($arg === '--') {
                $tokens[] = $arg;
                $stopParsing = true;
                continue;
            }

            $len = strlen($arg);
            $i = 0;
            $hasQuotes = false;
            while ($i < $len) {
                $char = $arg[$i];
                if (!$inQuote && ($char === '"' || $char === "'")) {
                    $inQuote = true;
                    $quoteChar = $char;
                    $hasQuotes = true;
                    $i++;
                    continue;
                }

                if ($inQuote && $char === $quoteChar) {
                    $inQuote = false;
                    $quoteChar = '';
                    $i++;
                    continue;
                }

                if (!$inQuote && $char === ' ') {
                    if ($current !== '' || $hasQuotes) {
                        // Add token if non-empty OR if it had quotes (preserves empty strings)
                        $tokens[] = $current;
                        $current = '';
                        $hasQuotes = false;
                    }
                    $i++;
                    continue;
                }

                $current .= $char;
                $i++;
            }
            if (!$inQuote && ($current !== '' || $hasQuotes)) {
                // Add final token if non-empty OR if it had quotes (preserves empty strings)
                $tokens[] = $current;
                $current = '';
                $hasQuotes = false;
            }
        }
        if ($current !== '') {
            $tokens[] = $current;
        }

        // Capture unclosed quote error
        if ($inQuote && $input !== null) {
            $input->addError("Unclosed quote: expected closing {$quoteChar}");
        }

        return $tokens;
    }
}
