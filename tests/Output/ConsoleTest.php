<?php

declare(strict_types=1);

use CodeIgniter\Console\Output\Console;
use CodeIgniter\Console\Output\Theme;

describe('Console', function () {
    describe('constructor', function () {
        it('sets default theme', function () {
            $console = new Console();

            expect($console->theme)->toBe('default');
        });

        it('sets custom theme', function () {
            $console = new Console(theme: 'ocean');

            expect($console->theme)->toBe('ocean');
        });

        it('sets default environment to production', function () {
            $console = new Console();

            expect($console->isDevelopment())->toBeFalse();
        });

        it('sets custom environment', function () {
            $console = new Console(environment: 'development');

            expect($console->isDevelopment())->toBeTrue();
        });
    });

    describe('write method', function () {
        it('returns text wrapped in ANSI codes', function () {
            $console = new Console();
            $result = $console->write('Hello');

            expect($result)->toContain("\033[");
            expect($result)->toContain("Hello");
            expect($result)->toContain("\033[0m");
        });

        it('applies foreground color', function () {
            $console = new Console();
            $result = $console->write('Hello', 'red');

            // Should contain color code for red (9 in 8-bit palette)
            expect($result)->toContain('38;5;9');
        });

        it('applies background color', function () {
            $console = new Console();
            $result = $console->write('Hello', 'white', 'red');

            // Should contain background color code for red
            expect($result)->toContain('48;5;9');
        });

        it('applies format bold', function () {
            $console = new Console();
            $result = $console->write('Hello', format: 'bold');

            expect($result)->toContain(';1m');
        });

        it('applies format italic', function () {
            $console = new Console();
            $result = $console->write('Hello', format: 'italic');

            expect($result)->toContain(';3m');
        });

        it('applies format underline', function () {
            $console = new Console();
            $result = $console->write('Hello', format: 'underline');

            expect($result)->toContain(';4m');
        });

        it('applies format blink', function () {
            $console = new Console();
            $result = $console->write('Hello', format: 'blink');

            expect($result)->toContain(';5m');
        });

        it('applies format reverse', function () {
            $console = new Console();
            $result = $console->write('Hello', format: 'reverse');

            expect($result)->toContain(';7m');
        });

        it('applies format strikethrough', function () {
            $console = new Console();
            $result = $console->write('Hello', format: 'strikethrough');

            expect($result)->toContain(';9m');
        });

        it('uses theme text color when no foreground specified', function () {
            $console = new Console(theme: 'default');
            $result = $console->write('Hello');

            // Default theme text color is white (15)
            expect($result)->toContain('38;5;15');
        });

        it('allows combining foreground and background', function () {
            $console = new Console();
            $result = $console->write('Hello', 'lime', 'navy');

            expect($result)->toContain('38;5;10'); // lime
            expect($result)->toContain('48;5;4');  // navy
        });

        it('allows combining color and format', function () {
            $console = new Console();
            $result = $console->write('Hello', 'red', '', 'bold');

            expect($result)->toContain('38;5;9');  // red
            expect($result)->toContain(';1m');     // bold
        });

        it('handles empty foreground with valid format', function () {
            $console = new Console();
            $result = $console->write('Hello', '', '', 'bold');

            expect($result)->toContain('38;5;15'); // default text color
            expect($result)->toContain(';1m');     // bold
        });
    });

    describe('line method', function () {
        it('echoes text with newline', function () {
            $console = new Console();

            ob_start();
            $console->line('Hello');
            $output = ob_get_clean();

            expect($output)->toContain('Hello');
            expect($output)->toEndWith("\n");
        });

        it('handles empty line', function () {
            $console = new Console();

            ob_start();
            $console->line();
            $output = ob_get_clean();

            // Even empty text gets theme color codes, just check for newline
            expect($output)->toEndWith("\n");
        });
    });

    describe('success method', function () {
        it('outputs success symbol', function () {
            $console = new Console();

            ob_start();
            $console->success('Operation completed');
            $output = ob_get_clean();

            expect($output)->toContain('✓');
            expect($output)->toContain('Operation completed');
        });

        it('uses success color from theme', function () {
            $console = new Console(theme: 'default');

            ob_start();
            $console->success('OK');
            $output = ob_get_clean();

            // Default theme success color is lime (10)
            expect($output)->toContain('38;5;10');
        });

        it('ends with newline', function () {
            $console = new Console();

            ob_start();
            $console->success('Test');
            $output = ob_get_clean();

            expect($output)->toEndWith("\n");
        });
    });

    describe('warning method', function () {
        it('outputs warning symbol', function () {
            $console = new Console();

            ob_start();
            $console->warning('Be careful');
            $output = ob_get_clean();

            expect($output)->toContain('⚠');
            expect($output)->toContain('Be careful');
        });

        it('uses warning color from theme', function () {
            $console = new Console(theme: 'default');

            ob_start();
            $console->warning('Alert');
            $output = ob_get_clean();

            // Default theme warning color is yellow (11)
            expect($output)->toContain('38;5;11');
        });
    });

    describe('error method', function () {
        it('outputs error symbol', function () {
            $console = new Console();

            ob_start();
            $console->error('Something failed');
            $output = ob_get_clean();

            expect($output)->toContain('✗');
            expect($output)->toContain('Something failed');
        });

        it('uses error color from theme', function () {
            $console = new Console(theme: 'default');

            ob_start();
            $console->error('Fail');
            $output = ob_get_clean();

            // Default theme error color is red (9)
            expect($output)->toContain('38;5;9');
        });
    });

    describe('info method', function () {
        it('outputs info symbol', function () {
            $console = new Console();

            ob_start();
            $console->info('Here is information');
            $output = ob_get_clean();

            expect($output)->toContain('ℹ');
            expect($output)->toContain('Here is information');
        });

        it('uses info color from theme', function () {
            $console = new Console(theme: 'default');

            ob_start();
            $console->info('Notice');
            $output = ob_get_clean();

            // Default theme info color is blue (12)
            expect($output)->toContain('38;5;12');
        });
    });

    describe('debug method', function () {
        it('does not output in production environment', function () {
            $console = new Console(environment: 'production');

            ob_start();
            $console->debug('Debug info');
            $output = ob_get_clean();

            expect($output)->toBe('');
        });

        it('outputs in development environment', function () {
            $console = new Console(environment: 'development');

            ob_start();
            $console->debug('Debug info');
            $output = ob_get_clean();

            expect($output)->toContain('🐛');
            expect($output)->toContain('Debug info');
        });

        it('uses purple color', function () {
            $console = new Console(environment: 'development');

            ob_start();
            $console->debug('Test');
            $output = ob_get_clean();

            // Purple is color code 5
            expect($output)->toContain('38;5;5');
        });
    });

    describe('title method', function () {
        it('outputs title with borders', function () {
            $console = new Console();

            ob_start();
            $console->title('Main Section');
            $output = ob_get_clean();

            expect($output)->toContain('===');
            expect($output)->toContain('Main Section');
        });

        it('applies bold format to title', function () {
            $console = new Console();

            ob_start();
            $console->title('Test');
            $output = ob_get_clean();

            expect($output)->toContain(';1m');
        });

        it('uses title color from theme', function () {
            $console = new Console(theme: 'default');

            ob_start();
            $console->title('Header');
            $output = ob_get_clean();

            // Default theme title color is aqua (14)
            expect($output)->toContain('38;5;14');
        });
    });

    describe('fatal method', function () {
        it('outputs fatal error symbol and message', function () {
            $console = new Console();
            $exception = new Exception('Critical error');

            ob_start();
            $console->fatal($exception);
            $output = ob_get_clean();

            expect($output)->toContain('💥');
            expect($output)->toContain('Fatal Error');
            expect($output)->toContain('Critical error');
        });

        it('outputs exception stack trace', function () {
            $console = new Console();
            $exception = new Exception('Test exception');

            ob_start();
            $console->fatal($exception);
            $output = ob_get_clean();

            // Stack trace should contain file path and line numbers from exception
            expect($output)->toContain('ConsoleTest.php');
        });

        it('uses error theme color for fatal', function () {
            $console = new Console(theme: 'default');
            $exception = new Exception('Error');

            ob_start();
            $console->fatal($exception);
            $output = ob_get_clean();

            // Should use error color (red in default theme)
            expect($output)->toContain('38;5;9');
        });
    });

    describe('raw method', function () {
        it('outputs text without ANSI codes', function () {
            $console = new Console();

            ob_start();
            $console->raw('Plain text');
            $output = ob_get_clean();

            expect($output)->toBe('Plain text');
            expect($output)->not()->toContain("\033[");
        });

        it('does not add newline', function () {
            $console = new Console();

            ob_start();
            $console->raw('Test');
            $output = ob_get_clean();

            expect($output)->not()->toEndWith("\n");
        });
    });

    describe('setTheme method', function () {
        it('changes the active theme', function () {
            $console = new Console(theme: 'default');
            expect($console->theme)->toBe('default');

            $result = $console->setTheme('ocean');

            expect($console->theme)->toBe('ocean');
        });

        it('returns self for method chaining', function () {
            $console = new Console();
            $result = $console->setTheme('pastel');

            expect($result)->toBe($console);
        });

        it('throws exception for non-existent theme', function () {
            expect(function () {
                $console = new Console();
                $console->setTheme('nonexistent-theme');
            })->toThrow(InvalidArgumentException::class, "Theme 'nonexistent-theme' does not exist");
        });

        it('works with all built-in themes', function () {
            $console = new Console();
            $themes = ['default', 'monochromatic', 'high-contrast', 'pastel', 'amber', 'forest', 'ocean', 'sunset'];

            foreach ($themes as $theme) {
                expect(function () use ($console, $theme) {
                    $console->setTheme($theme);
                })->not()->toThrow(Exception::class);

                expect($console->theme)->toBe($theme);
            }
        });
    });

    describe('isDevelopment method', function () {
        it('returns true when environment is development', function () {
            $console = new Console(environment: 'development');

            expect($console->isDevelopment())->toBeTrue();
        });

        it('returns false when environment is production', function () {
            $console = new Console(environment: 'production');

            expect($console->isDevelopment())->toBeFalse();
        });

        it('returns false for other environment values', function () {
            $console = new Console(environment: 'staging');

            expect($console->isDevelopment())->toBeFalse();
        });
    });

    describe('theme integration', function () {
        it('uses theme colors in message methods', function () {
            Theme::register('test-console-theme', [
                'success' => 'emerald',
                'error' => 'crimson',
                'warning' => 'gold',
                'info' => 'steelblue',
                'text' => 'white',
                'title' => 'aqua',
            ]);

            $console = new Console(theme: 'test-console-theme');

            ob_start();
            $console->success('Test');
            $output = ob_get_clean();

            // Emerald is 29
            expect($output)->toContain('38;5;29');
        });

        it('handles invalid theme gracefully in getThemeColor', function () {
            $console = new Console(theme: 'default');

            // This should not throw, but use default colors
            ob_start();
            $console->success('Test');
            $output = ob_get_clean();

            expect($output)->not()->toBe('');
        });
    });

    describe('chaining and composition', function () {
        it('allows method chaining with setTheme', function () {
            $console = new Console();

            $result = $console->setTheme('ocean');

            expect($result->theme)->toBe('ocean');
        });

        it('can compose multiple write calls on same line', function () {
            $console = new Console();

            ob_start();
            echo $console->write('Status: ', 'white');
            echo $console->write('OK', 'lime');
            echo "\n";
            $output = ob_get_clean();

            expect($output)->toContain('Status:');
            expect($output)->toContain('OK');
            expect($output)->toEndWith("\n");
        });

        it('can output different message types in sequence', function () {
            $console = new Console(environment: 'development');

            ob_start();
            $console->info('Starting process');
            $console->success('Step 1 complete');
            $console->warning('Check configuration');
            $console->debug('Debug info');
            $console->line('Done');
            $output = ob_get_clean();

            expect($output)->toContain('ℹ');
            expect($output)->toContain('✓');
            expect($output)->toContain('⚠');
            expect($output)->toContain('🐛');
            expect($output)->toContain('Done');
        });
    });

    describe('with different color names', function () {
        it('works with numeric color codes', function () {
            $console = new Console();

            // Using numeric color code directly
            $result = $console->write('Test', '196');

            expect($result)->toContain('38;5;196');
        });

        it('works with registered custom colors', function () {
            Theme::registerColor('mycustom', 99);
            $console = new Console();

            $result = $console->write('Test', 'mycustom');

            expect($result)->toContain('38;5;99');
        });

        it('works with extended color names', function () {
            $console = new Console();

            $result = $console->write('Test', 'crimson');

            expect($result)->toContain('38;5;160');
        });
    });
});
