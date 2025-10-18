<?php

declare(strict_types=1);

use CodeIgniter\Console\Parse\Input;
use CodeIgniter\Console\Parse\Tokenizer;

describe('Tokenizer', function () {
    describe('basic tokenization', function () {
        it('splits simple arguments', function () {
            $argv = ['script.php', 'arg1', 'arg2', 'arg3'];
            $tokens = Tokenizer::tokenize($argv);

            expect($tokens)->toBe(['arg1', 'arg2', 'arg3']);
        });

        it('handles single argument', function () {
            $argv = ['script.php', 'arg1'];
            $tokens = Tokenizer::tokenize($argv);

            expect($tokens)->toBe(['arg1']);
        });

        it('handles empty argv (just script)', function () {
            $argv = ['script.php'];
            $tokens = Tokenizer::tokenize($argv);

            expect($tokens)->toBe([]);
        });

        it('drops the script name', function () {
            $argv = ['path/to/script.php', 'arg1', 'arg2'];
            $tokens = Tokenizer::tokenize($argv);

            expect($tokens)->toBe(['arg1', 'arg2']);
        });
    });

    describe('quoted strings', function () {
        it('handles double-quoted strings', function () {
            $argv = ['script.php', '"hello world"', 'arg2'];
            $tokens = Tokenizer::tokenize($argv);

            expect($tokens)->toBe(['hello world', 'arg2']);
        });

        it('handles single-quoted strings', function () {
            $argv = ['script.php', "'hello world'", 'arg2'];
            $tokens = Tokenizer::tokenize($argv);

            expect($tokens)->toBe(['hello world', 'arg2']);
        });

        it('preserves spaces within quotes', function () {
            $argv = ['script.php', '"multiple   spaces"'];
            $tokens = Tokenizer::tokenize($argv);

            expect($tokens)->toBe(['multiple   spaces']);
        });

        it('handles mixed quotes in single argument', function () {
            $argv = ['script.php', '"part1"\'part2\''];
            $tokens = Tokenizer::tokenize($argv);

            expect($tokens)->toBe(['part1part2']);
        });

        it('handles empty quoted strings', function () {
            $argv = ['script.php', '""', 'arg2'];
            $tokens = Tokenizer::tokenize($argv);

            expect($tokens)->toBe(['', 'arg2']);
        });

        it('handles single quotes within double quotes', function () {
            $argv = ['script.php', '"it\'s working"'];
            $tokens = Tokenizer::tokenize($argv);

            expect($tokens)->toBe(["it's working"]);
        });

        it('handles double quotes within single quotes', function () {
            $argv = ['script.php', '\'say "hello"\''];
            $tokens = Tokenizer::tokenize($argv);

            expect($tokens)->toBe(['say "hello"']);
        });

        it('handles quotes at different positions', function () {
            $argv = ['script.php', 'prefix"quoted"suffix'];
            $tokens = Tokenizer::tokenize($argv);

            expect($tokens)->toBe(['prefixquotedsuffix']);
        });
    });

    describe('-- token (stop parsing)', function () {
        it('stops parsing after -- token', function () {
            $argv = ['script.php', 'arg1', '--', 'arg2', '--option'];
            $tokens = Tokenizer::tokenize($argv);

            expect($tokens)->toBe(['arg1', '--', 'arg2', '--option']);
        });

        it('includes -- in tokens', function () {
            $argv = ['script.php', '--'];
            $tokens = Tokenizer::tokenize($argv);

            expect($tokens)->toBe(['--']);
        });

        it('preserves everything after -- as is', function () {
            $argv = ['script.php', 'before', '--', 'after with spaces', '--flag=value'];
            $tokens = Tokenizer::tokenize($argv);

            expect($tokens)->toBe(['before', '--', 'after with spaces', '--flag=value']);
        });
    });

    describe('special characters and edge cases', function () {
        it('handles options with equals sign', function () {
            $argv = ['script.php', '--name=value'];
            $tokens = Tokenizer::tokenize($argv);

            expect($tokens)->toBe(['--name=value']);
        });

        it('handles short options', function () {
            $argv = ['script.php', '-v'];
            $tokens = Tokenizer::tokenize($argv);

            expect($tokens)->toBe(['-v']);
        });

        it('handles multiple arguments with dashes', function () {
            $argv = ['script.php', '--option1', '--option2=value', '-v'];
            $tokens = Tokenizer::tokenize($argv);

            expect($tokens)->toBe(['--option1', '--option2=value', '-v']);
        });

        it('handles equals signs within quoted strings', function () {
            $argv = ['script.php', '"key=value"'];
            $tokens = Tokenizer::tokenize($argv);

            expect($tokens)->toBe(['key=value']);
        });

        it('handles numeric arguments', function () {
            $argv = ['script.php', '123', '45.67', '-42'];
            $tokens = Tokenizer::tokenize($argv);

            expect($tokens)->toBe(['123', '45.67', '-42']);
        });

        it('handles special characters', function () {
            $argv = ['script.php', 'test@example.com', 'path/to/file', 'underscore_value'];
            $tokens = Tokenizer::tokenize($argv);

            expect($tokens)->toBe(['test@example.com', 'path/to/file', 'underscore_value']);
        });

        it('handles arguments with multiple spaces between them', function () {
            $argv = ['script.php', 'arg1', 'arg2', 'arg3'];
            $tokens = Tokenizer::tokenize($argv);

            expect($tokens)->toBe(['arg1', 'arg2', 'arg3']);
        });
    });

    describe('unclosed quote error handling', function () {
        it('captures unclosed quote error when Input is provided', function () {
            $argv = ['script.php', '"unclosed'];
            $input = new Input();
            $tokens = Tokenizer::tokenize($argv, $input);

            expect($input->errors())->toContain("Unclosed quote: expected closing \"");
        });

        it('captures unclosed single quote error', function () {
            $argv = ['script.php', "'unclosed"];
            $input = new Input();
            $tokens = Tokenizer::tokenize($argv, $input);

            expect($input->errors())->toContain("Unclosed quote: expected closing '");
        });

        it('does not add error when Input is null', function () {
            $argv = ['script.php', '"unclosed'];
            $tokens = Tokenizer::tokenize($argv, null);

            // Should not throw - simply returns tokens
            expect($tokens)->toBeArray();
        });

        it('processes tokens even with unclosed quote', function () {
            $argv = ['script.php', 'arg1', '"unclosed'];
            $input = new Input();
            $tokens = Tokenizer::tokenize($argv, $input);

            expect($tokens)->toBe(['arg1', 'unclosed']);
            expect($input->hasErrors())->toBeTrue();
        });
    });

    describe('complex real-world scenarios', function () {
        it('handles command with options and quoted arguments', function () {
            $argv = ['script.php', 'migrate', '--table="users"', '--direction=up'];
            $tokens = Tokenizer::tokenize($argv);

            expect($tokens)->toBe(['migrate', '--table=users', '--direction=up']);
        });

        it('handles file paths with spaces', function () {
            $argv = ['script.php', 'publish', '"path/to/my documents/file.txt"'];
            $tokens = Tokenizer::tokenize($argv);

            expect($tokens)->toBe(['publish', 'path/to/my documents/file.txt']);
        });

        it('handles mixed quoted and unquoted in one argument', function () {
            $argv = ['script.php', '"hello"world"again"'];
            $tokens = Tokenizer::tokenize($argv);

            expect($tokens)->toBe(['helloworldagain']);
        });

        it('handles JSON-like string in quotes', function () {
            $argv = ['script.php', '\'{"key":"value"}\''];
            $tokens = Tokenizer::tokenize($argv);

            expect($tokens)->toBe(['{"key":"value"}']);
        });

        it('handles command with multiple flags and arguments', function () {
            $argv = ['script.php', 'command', '-v', '--debug', 'argument1', '--name=test', 'argument2'];
            $tokens = Tokenizer::tokenize($argv);

            expect($tokens)->toBe(['command', '-v', '--debug', 'argument1', '--name=test', 'argument2']);
        });

        it('handles -- followed by options that should be treated as arguments', function () {
            $argv = ['script.php', 'ls', '-la', '--', '-H', '--ignore-case'];
            $tokens = Tokenizer::tokenize($argv);

            expect($tokens)->toBe(['ls', '-la', '--', '-H', '--ignore-case']);
        });

        it('handles empty strings between arguments', function () {
            $argv = ['script.php', 'arg1', '""', 'arg3'];
            $tokens = Tokenizer::tokenize($argv);

            expect($tokens)->toBe(['arg1', '', 'arg3']);
        });
    });

    describe('array structure', function () {
        it('returns array with numeric keys starting from 0', function () {
            $argv = ['script.php', 'arg1', 'arg2', 'arg3'];
            $tokens = Tokenizer::tokenize($argv);

            expect($tokens)->toBeArray();
            expect(array_keys($tokens))->toBe([0, 1, 2]);
        });

        it('maintains order of tokens', function () {
            $argv = ['script.php', 'first', 'second', 'third', 'fourth', 'fifth'];
            $tokens = Tokenizer::tokenize($argv);

            expect($tokens)->toBe(['first', 'second', 'third', 'fourth', 'fifth']);
        });
    });
});
