<?php

declare(strict_types=1);

use CodeIgniter\Console\Parse\Input;

describe('Input', function () {
    describe('command parsing', function () {
        it('extracts command from first token', function () {
            $argv = ['script.php', 'migrate'];
            $input = Input::fromArgv($argv);

            expect($input->command())->toBe('migrate');
        });

        it('strips leading dashes from command', function () {
            $argv = ['script.php', '-migrate'];
            $input = Input::fromArgv($argv);

            expect($input->command())->toBe('migrate');
        });

        it('returns null when no command', function () {
            $argv = ['script.php'];
            $input = Input::fromArgv($argv);

            expect($input->command())->toBeNull();
        });
    });

    describe('arguments parsing', function () {
        it('collects non-option tokens as arguments', function () {
            $argv = ['script.php', 'command', 'arg1', 'arg2', 'arg3'];
            $input = Input::fromArgv($argv);

            expect($input->args())->toBe(['arg1', 'arg2', 'arg3']);
        });

        it('retrieves single argument by index', function () {
            $argv = ['script.php', 'command', 'first', 'second', 'third'];
            $input = Input::fromArgv($argv);

            expect($input->arg(0))->toBe('first');
            expect($input->arg(1))->toBe('second');
            expect($input->arg(2))->toBe('third');
        });

        it('returns default value for non-existent argument', function () {
            $argv = ['script.php', 'command', 'arg1'];
            $input = Input::fromArgv($argv);

            expect($input->arg(5, 'default'))->toBe('default');
            expect($input->arg(5))->toBeNull();
        });

        it('handles quoted arguments', function () {
            $argv = ['script.php', 'command', '"quoted argument"', 'plain'];
            $input = Input::fromArgv($argv);

            expect($input->args())->toBe(['quoted argument', 'plain']);
        });

        it('handles empty arguments list', function () {
            $argv = ['script.php', 'command'];
            $input = Input::fromArgv($argv);

            expect($input->args())->toBe([]);
        });

        it('preserves argument order', function () {
            $argv = ['script.php', 'cmd', 'alpha', 'beta', 'gamma', 'delta', 'epsilon'];
            $input = Input::fromArgv($argv);

            expect($input->args())->toBe(['alpha', 'beta', 'gamma', 'delta', 'epsilon']);
        });
    });

    describe('long options (--option)', function () {
        it('parses long option with equals sign', function () {
            $argv = ['script.php', 'cmd', '--name=value'];
            $input = Input::fromArgv($argv);

            expect($input->option('name'))->toBe('value');
            expect($input->has('name'))->toBeTrue();
        });

        it('parses long option with separate value', function () {
            $argv = ['script.php', 'cmd', '--name', 'value'];
            $input = Input::fromArgv($argv);

            expect($input->option('name'))->toBe('value');
        });

        it('treats long option as boolean flag when no value', function () {
            $argv = ['script.php', 'cmd', '--verbose'];
            $input = Input::fromArgv($argv);

            expect($input->option('verbose'))->toBeTrue();
        });

        it('uses default value for non-existent option', function () {
            $argv = ['script.php', 'cmd'];
            $input = Input::fromArgv($argv);

            expect($input->option('missing', 'default'))->toBe('default');
            expect($input->option('missing'))->toBeNull();
        });

        it('handles multiple long options', function () {
            $argv = ['script.php', 'cmd', '--name=john', '--age=30', '--active'];
            $input = Input::fromArgv($argv);

            expect($input->option('name'))->toBe('john');
            expect($input->option('age'))->toBe('30');
            expect($input->option('active'))->toBeTrue();
        });

        it('handles empty string option values', function () {
            $argv = ['script.php', 'cmd', '--text=""'];
            $input = Input::fromArgv($argv);

            expect($input->option('text'))->toBe('');
        });

        it('does not take option value from next token if it starts with dash', function () {
            $argv = ['script.php', 'cmd', '--name', '--other'];
            $input = Input::fromArgv($argv);

            expect($input->option('name'))->toBeTrue();
            expect($input->option('other'))->toBeTrue();
        });

        it('reports error for empty option name', function () {
            $argv = ['script.php', 'cmd', '--=value'];
            $input = Input::fromArgv($argv);

            expect($input->hasErrors())->toBeTrue();
            expect($input->errors())->toContain("Invalid option: option name cannot be empty (got '--=')");
        });

        it('last option wins on duplicates', function () {
            $argv = ['script.php', 'cmd', '--name=first', '--name=second'];
            $input = Input::fromArgv($argv);

            expect($input->option('name'))->toBe('second');
        });
    });

    describe('short options (-o)', function () {
        it('parses single short option with value', function () {
            $argv = ['script.php', 'cmd', '-n', 'value'];
            $input = Input::fromArgv($argv);

            expect($input->option('n'))->toBe('value');
        });

        it('parses single short option as boolean flag', function () {
            $argv = ['script.php', 'cmd', '-v'];
            $input = Input::fromArgv($argv);

            expect($input->option('v'))->toBeTrue();
        });

        it('parses multiple short flags together', function () {
            $argv = ['script.php', 'cmd', '-vfd'];
            $input = Input::fromArgv($argv);

            expect($input->option('v'))->toBeTrue();
            expect($input->option('f'))->toBeTrue();
            expect($input->option('d'))->toBeTrue();
        });

        it('does not take value for multiple short flags', function () {
            $argv = ['script.php', 'cmd', '-vfd', 'arg'];
            $input = Input::fromArgv($argv);

            expect($input->option('v'))->toBeTrue();
            expect($input->option('f'))->toBeTrue();
            expect($input->option('d'))->toBeTrue();
            expect($input->args())->toBe(['arg']);
        });

        it('does not take next token if it starts with dash', function () {
            $argv = ['script.php', 'cmd', '-n', '--other'];
            $input = Input::fromArgv($argv);

            expect($input->option('n'))->toBeTrue();
            expect($input->option('other'))->toBeTrue();
        });

        it('reports error for lone dash', function () {
            $argv = ['script.php', 'cmd', '-'];
            $input = Input::fromArgv($argv);

            expect($input->hasErrors())->toBeTrue();
            expect($input->errors())->toContain("Invalid option: lone dash '-' with no flag");
        });
    });

    describe('boolean-like value casting', function () {
        it('casts "true" to boolean true', function () {
            $argv = ['script.php', 'cmd', '--flag=true'];
            $input = Input::fromArgv($argv);

            expect($input->option('flag'))->toBeTrue();
        });

        it('casts "false" to boolean false', function () {
            $argv = ['script.php', 'cmd', '--flag=false'];
            $input = Input::fromArgv($argv);

            expect($input->option('flag'))->toBeFalse();
        });

        it('casts "1" to boolean true', function () {
            $argv = ['script.php', 'cmd', '--flag=1'];
            $input = Input::fromArgv($argv);

            expect($input->option('flag'))->toBeTrue();
        });

        it('casts "0" to boolean false', function () {
            $argv = ['script.php', 'cmd', '--flag=0'];
            $input = Input::fromArgv($argv);

            expect($input->option('flag'))->toBeFalse();
        });

        it('casts "yes" to boolean true', function () {
            $argv = ['script.php', 'cmd', '--flag=yes'];
            $input = Input::fromArgv($argv);

            expect($input->option('flag'))->toBeTrue();
        });

        it('casts "no" to boolean false', function () {
            $argv = ['script.php', 'cmd', '--flag=no'];
            $input = Input::fromArgv($argv);

            expect($input->option('flag'))->toBeFalse();
        });

        it('is case-insensitive for boolean casting', function () {
            $argv = ['script.php', 'cmd', '--f1=TRUE', '--f2=FALSE', '--f3=YES', '--f4=NO'];
            $input = Input::fromArgv($argv);

            expect($input->option('f1'))->toBeTrue();
            expect($input->option('f2'))->toBeFalse();
            expect($input->option('f3'))->toBeTrue();
            expect($input->option('f4'))->toBeFalse();
        });

        it('preserves non-boolean strings', function () {
            $argv = ['script.php', 'cmd', '--text=hello', '--num=42'];
            $input = Input::fromArgv($argv);

            expect($input->option('text'))->toBe('hello');
            expect($input->option('num'))->toBe('42');
        });
    });

    describe('complex scenarios', function () {
        it('parses mixed options and arguments', function () {
            $argv = ['script.php', 'migrate', '--direction=up', 'users', '--verbose', 'posts'];
            $input = Input::fromArgv($argv);

            expect($input->command())->toBe('migrate');
            expect($input->option('direction'))->toBe('up');
            // verbose is treated as an option taking 'posts' as its value
            expect($input->option('verbose'))->toBe('posts');
            expect($input->args())->toBe(['users']);
        });

        it('handles real-world command example', function () {
            $argv = ['script.php', 'publish', '-v', '--dir=storage', '--force', 'views', 'assets'];
            $input = Input::fromArgv($argv);

            expect($input->command())->toBe('publish');
            expect($input->option('v'))->toBeTrue();
            expect($input->option('dir'))->toBe('storage');
            // force is treated as an option taking 'views' as its value
            expect($input->option('force'))->toBe('views');
            expect($input->args())->toBe(['assets']);
        });

        it('handles multiple short flags with values', function () {
            $argv = ['script.php', 'cmd', '-o', 'output.txt', '-i', 'input.txt'];
            $input = Input::fromArgv($argv);

            expect($input->option('o'))->toBe('output.txt');
            expect($input->option('i'))->toBe('input.txt');
        });

        it('handles quoted values in options', function () {
            $argv = ['script.php', 'cmd', '--message="Hello World"', 'arg1'];
            $input = Input::fromArgv($argv);

            expect($input->option('message'))->toBe('Hello World');
            expect($input->args())->toBe(['arg1']);
        });

        it('mixes long and short options', function () {
            $argv = ['script.php', 'cmd', '-vfd', '--name=test', 'arg1', '--active'];
            $input = Input::fromArgv($argv);

            expect($input->option('v'))->toBeTrue();
            expect($input->option('f'))->toBeTrue();
            expect($input->option('d'))->toBeTrue();
            expect($input->option('name'))->toBe('test');
            expect($input->option('active'))->toBeTrue();
            expect($input->args())->toBe(['arg1']);
        });
    });

    describe('option retrieval', function () {
        it('returns all options', function () {
            $argv = ['script.php', 'cmd', '--name=john', '-v', '--active'];
            $input = Input::fromArgv($argv);

            $options = $input->options();
            expect($options)->toHaveKey('name');
            expect($options)->toHaveKey('v');
            expect($options)->toHaveKey('active');
            expect($options['name'])->toBe('john');
            expect($options['v'])->toBeTrue();
            expect($options['active'])->toBeTrue();
        });

        it('checks if option exists', function () {
            $argv = ['script.php', 'cmd', '--name=john'];
            $input = Input::fromArgv($argv);

            expect($input->has('name'))->toBeTrue();
            expect($input->has('missing'))->toBeFalse();
        });

        it('handles has() for boolean flags', function () {
            $argv = ['script.php', 'cmd', '-v'];
            $input = Input::fromArgv($argv);

            expect($input->has('v'))->toBeTrue();
            expect($input->has('x'))->toBeFalse();
        });
    });

    describe('error handling', function () {
        it('starts with no errors', function () {
            $argv = ['script.php', 'cmd', '--name=value'];
            $input = Input::fromArgv($argv);

            expect($input->hasErrors())->toBeFalse();
            expect($input->errors())->toBe([]);
        });

        it('can add errors manually', function () {
            $input = new Input();
            $input->addError('Test error');

            expect($input->hasErrors())->toBeTrue();
            expect($input->errors())->toContain('Test error');
        });

        it('retrieves first error', function () {
            $input = new Input();
            $input->addError('First error');
            $input->addError('Second error');

            expect($input->firstError())->toBe('First error');
        });

        it('returns null for firstError when no errors', function () {
            $argv = ['script.php', 'cmd'];
            $input = Input::fromArgv($argv);

            expect($input->firstError())->toBeNull();
        });

        it('clears all errors', function () {
            $input = new Input();
            $input->addError('Error 1');
            $input->addError('Error 2');

            expect($input->hasErrors())->toBeTrue();

            $input->clearErrors();

            expect($input->hasErrors())->toBeFalse();
            expect($input->errors())->toBe([]);
        });

        it('collects parsing errors from Tokenizer', function () {
            $argv = ['script.php', 'cmd', '"unclosed'];
            $input = Input::fromArgv($argv);

            expect($input->hasErrors())->toBeTrue();
            expect($input->firstError())->toContain('Unclosed quote');
        });

        it('collects multiple parsing errors', function () {
            $argv = ['script.php', 'cmd', '--=value', '-'];
            $input = Input::fromArgv($argv);

            expect($input->hasErrors())->toBeTrue();
            expect(count($input->errors()))->toBeGreaterThanOrEqual(2);
        });
    });

    describe('edge cases', function () {
        it('handles option with numeric name', function () {
            $argv = ['script.php', 'cmd', '-1', '-2'];
            $input = Input::fromArgv($argv);

            expect($input->option('1'))->toBeTrue();
            expect($input->option('2'))->toBeTrue();
        });

        it('handles option values with equals signs', function () {
            $argv = ['script.php', 'cmd', '--query=a=b=c'];
            $input = Input::fromArgv($argv);

            expect($input->option('query'))->toBe('a=b=c');
        });

        it('handles empty argv', function () {
            $argv = ['script.php'];
            $input = Input::fromArgv($argv);

            expect($input->command())->toBeNull();
            expect($input->args())->toBe([]);
            expect($input->options())->toBe([]);
        });

        it('handles only options no command', function () {
            $argv = ['script.php', '--name=value'];
            $input = Input::fromArgv($argv);

            // First token is treated as command, with leading dashes stripped
            expect($input->command())->toBe('name=value');
            // No options are parsed since the first token is treated as command
            expect($input->options())->toBe([]);
        });

        it('handles numeric arguments', function () {
            $argv = ['script.php', 'cmd', '123', '45.67'];
            $input = Input::fromArgv($argv);

            expect($input->args())->toContain('123', '45.67');
        });

        it('handles special characters in arguments', function () {
            $argv = ['script.php', 'cmd', 'test@example.com', 'path/to/file'];
            $input = Input::fromArgv($argv);

            expect($input->args())->toContain('test@example.com', 'path/to/file');
        });
    });
});
