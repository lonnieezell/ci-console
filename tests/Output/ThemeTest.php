<?php

declare(strict_types=1);

use CodeIgniter\Console\Output\Theme;

describe('Theme', function () {
    describe('color palette', function () {
        it('has standard colors', function () {
            expect(Theme::getColor('black'))->toBe(0);
            expect(Theme::getColor('red'))->toBe(9);
            expect(Theme::getColor('green'))->toBe(2);
            expect(Theme::getColor('white'))->toBe(15);
        });

        it('has extended colors', function () {
            expect(Theme::getColor('lime'))->toBe(10);
            expect(Theme::getColor('brightblue'))->toBe(51);
            expect(Theme::getColor('brightyellow'))->toBe(226);
        });

        it('returns null for non-existent color', function () {
            expect(Theme::getColor('nonexistent'))->toBeNull();
        });

        it('checks if color exists', function () {
            expect(Theme::hasColor('red'))->toBeTrue();
            expect(Theme::hasColor('lime'))->toBeTrue();
            expect(Theme::hasColor('nonexistent'))->toBeFalse();
        });
    });

    describe('register color', function () {
        it('registers a new color', function () {
            Theme::registerColor('customred', 196);

            expect(Theme::getColor('customred'))->toBe(196);
            expect(Theme::hasColor('customred'))->toBeTrue();
        });

        it('throws exception for invalid color code below range', function () {
            expect(function () {
                Theme::registerColor('invalid', -1);
            })->toThrow(InvalidArgumentException::class, 'Color code must be between 0 and 255');
        });

        it('throws exception for invalid color code above range', function () {
            expect(function () {
                Theme::registerColor('invalid', 256);
            })->toThrow(InvalidArgumentException::class, 'Color code must be between 0 and 255');
        });

        it('allows color codes on boundaries', function () {
            Theme::registerColor('min', 0);
            Theme::registerColor('max', 255);

            expect(Theme::getColor('min'))->toBe(0);
            expect(Theme::getColor('max'))->toBe(255);
        });
    });

    describe('register palette', function () {
        it('registers multiple colors at once', function () {
            Theme::registerPalette([
                'darkred' => 52,
                'darkgreen' => 22,
                'darkblue' => 18,
            ]);

            expect(Theme::getColor('darkred'))->toBe(52);
            expect(Theme::getColor('darkgreen'))->toBe(22);
            expect(Theme::getColor('darkblue'))->toBe(18);
        });

        it('throws exception if any color in palette is invalid', function () {
            expect(function () {
                Theme::registerPalette([
                    'valid' => 100,
                    'invalid' => 300,
                ]);
            })->toThrow(InvalidArgumentException::class);
        });
    });

    describe('unregister color', function () {
        it('unregisters an existing color', function () {
            Theme::registerColor('temp', 100);
            expect(Theme::hasColor('temp'))->toBeTrue();

            $result = Theme::unregisterColor('temp');

            expect($result)->toBeTrue();
            expect(Theme::hasColor('temp'))->toBeFalse();
        });

        it('returns false when unregistering non-existent color', function () {
            $result = Theme::unregisterColor('nonexistent');

            expect($result)->toBeFalse();
        });
    });

    describe('get palette', function () {
        it('returns all colors', function () {
            $palette = Theme::getPalette();

            expect($palette)->toBeArray();
            expect($palette)->toHaveKey('red');
            expect($palette)->toHaveKey('lime');
            expect($palette['red'])->toBe(9);
        });

        it('returns a copy not a reference', function () {
            $palette = Theme::getPalette();
            $palette['test'] = 999;

            // Original palette should not be modified
            expect(Theme::hasColor('test'))->toBeFalse();
        });
    });

    describe('built-in themes', function () {
        it('has default theme', function () {
            expect(Theme::exists('default'))->toBeTrue();
        });

        it('has all built-in themes', function () {
            $themes = ['default', 'monochromatic', 'high-contrast', 'pastel', 'amber', 'forest', 'ocean', 'sunset'];

            foreach ($themes as $theme) {
                expect(Theme::exists($theme))->toBeTrue();
            }
        });

        it('gets theme by name', function () {
            $theme = Theme::get('default');

            expect($theme)->toBeArray();
            expect($theme)->toHaveKey('success');
            expect($theme)->toHaveKey('error');
            expect($theme)->toHaveKey('warning');
            expect($theme)->toHaveKey('info');
            expect($theme)->toHaveKey('text');
            expect($theme)->toHaveKey('title');
        });

        it('throws exception for non-existent theme', function () {
            expect(function () {
                Theme::get('nonexistent');
            })->toThrow(InvalidArgumentException::class, "Theme 'nonexistent' does not exist");
        });

        it('lists all theme names', function () {
            $themes = Theme::list();

            expect($themes)->toContain('default');
            expect($themes)->toContain('monochromatic');
            expect($themes)->toContain('forest');
            expect($themes)->toContain('ocean');
        });

        it('gets all themes', function () {
            $all = Theme::all();

            expect($all)->toBeArray();
            expect($all)->toHaveKey('default');
            expect($all['default'])->toBeArray();
        });
    });

    describe('register theme', function () {
        it('registers a new theme with validation', function () {
            Theme::register('test-theme', [
                'success' => 'lime',
                'error' => 'red',
                'warning' => 'yellow',
                'info' => 'blue',
                'text' => 'white',
                'title' => 'aqua',
            ]);

            expect(Theme::exists('test-theme'))->toBeTrue();
            $theme = Theme::get('test-theme');
            expect($theme['success'])->toBe('lime');
        });

        it('validates theme colors by default', function () {
            expect(function () {
                Theme::register('invalid-theme', [
                    'success' => 'nonexistent-color',
                    'error' => 'red',
                ]);
            })->toThrow(InvalidArgumentException::class, 'Theme references non-existent colors');
        });

        it('skips validation when validate=false', function () {
            Theme::register('unvalidated-theme', [
                'success' => 'nonexistent-color',
                'error' => 'also-nonexistent',
            ], validate: false);

            expect(Theme::exists('unvalidated-theme'))->toBeTrue();
        });

        it('replaces existing theme', function () {
            Theme::register('replaceable', ['success' => 'lime']);
            Theme::register('replaceable', ['success' => 'red']);

            $theme = Theme::get('replaceable');
            expect($theme['success'])->toBe('red');
        });
    });

    describe('unregister theme', function () {
        it('unregisters an existing theme', function () {
            Theme::register('temp-theme', ['success' => 'lime'], validate: false);
            expect(Theme::exists('temp-theme'))->toBeTrue();

            $result = Theme::unregister('temp-theme');

            expect($result)->toBeTrue();
            expect(Theme::exists('temp-theme'))->toBeFalse();
        });

        it('returns false when unregistering non-existent theme', function () {
            $result = Theme::unregister('nonexistent');

            expect($result)->toBeFalse();
        });
    });

    describe('validate theme', function () {
        it('validates that all colors in theme exist', function () {
            expect(function () {
                Theme::validateTheme(['success' => 'lime', 'error' => 'red']);
            })->not()->toThrow(Exception::class);
        });

        it('throws exception when color does not exist', function () {
            expect(function () {
                Theme::validateTheme(['success' => 'nonexistent']);
            })->toThrow(InvalidArgumentException::class, 'Theme references non-existent colors');
        });

        it('lists all missing colors in exception message', function () {
            expect(function () {
                Theme::validateTheme([
                    'success' => 'missing1',
                    'error' => 'missing2',
                ]);
            })->toThrow(InvalidArgumentException::class, 'missing1');
        });

        it('works with built-in theme colors', function () {
            expect(function () {
                $defaultTheme = Theme::get('default');
                Theme::validateTheme($defaultTheme);
            })->not()->toThrow(Exception::class);
        });
    });

    describe('update theme', function () {
        it('updates specific colors in a theme', function () {
            Theme::register('updatable', [
                'success' => 'lime',
                'error' => 'red',
                'warning' => 'yellow',
            ], validate: false);

            Theme::updateTheme('updatable', ['success' => 'emerald'], validate: false);

            $theme = Theme::get('updatable');
            expect($theme['success'])->toBe('emerald');
            // Other colors should remain unchanged
            expect($theme['error'])->toBe('red');
            expect($theme['warning'])->toBe('yellow');
        });

        it('throws exception when updating non-existent theme', function () {
            expect(function () {
                Theme::updateTheme('nonexistent', ['success' => 'lime']);
            })->toThrow(InvalidArgumentException::class, "Theme 'nonexistent' does not exist");
        });

        it('validates updated colors by default', function () {
            Theme::register('validatable', [
                'success' => 'lime',
                'error' => 'red',
            ], validate: false);

            expect(function () {
                Theme::updateTheme('validatable', ['success' => 'nonexistent']);
            })->toThrow(InvalidArgumentException::class, 'Theme references non-existent colors');
        });

        it('skips validation when validate=false', function () {
            Theme::register('skipped', ['success' => 'lime'], validate: false);

            expect(function () {
                Theme::updateTheme('skipped', ['error' => 'nonexistent'], validate: false);
            })->not()->toThrow(Exception::class);

            $theme = Theme::get('skipped');
            expect($theme['error'])->toBe('nonexistent');
        });

        it('can update multiple colors at once', function () {
            Theme::register('multi', [
                'success' => 'lime',
                'error' => 'red',
                'warning' => 'yellow',
                'info' => 'blue',
            ], validate: false);

            Theme::updateTheme('multi', [
                'success' => 'emerald',
                'error' => 'crimson',
            ], validate: false);

            $theme = Theme::get('multi');
            expect($theme['success'])->toBe('emerald');
            expect($theme['error'])->toBe('crimson');
            // Unchanged colors
            expect($theme['warning'])->toBe('yellow');
            expect($theme['info'])->toBe('blue');
        });
    });

    describe('integration', function () {
        it('works with a complete custom theme workflow', function () {
            // Register custom colors
            Theme::registerPalette([
                'custompurple' => 55,
                'custompink' => 170,
            ]);

            // Register a theme using custom colors
            Theme::register('custom-theme', [
                'success' => 'lime',
                'error' => 'custompurple',
                'warning' => 'custompink',
                'info' => 'blue',
                'text' => 'white',
                'title' => 'aqua',
            ]);

            // Update the theme
            Theme::updateTheme('custom-theme', ['success' => 'emerald'], validate: false);

            // Verify everything works
            $theme = Theme::get('custom-theme');
            expect($theme['success'])->toBe('emerald');
            expect($theme['error'])->toBe('custompurple');
            expect(Theme::getColor('custompurple'))->toBe(55);
        });

        it('can list and check multiple themes', function () {
            Theme::register('theme1', ['success' => 'lime'], validate: false);
            Theme::register('theme2', ['success' => 'red'], validate: false);

            $list = Theme::list();

            expect($list)->toContain('theme1');
            expect($list)->toContain('theme2');
            expect(count($list))->toBeGreaterThanOrEqual(2);
        });
    });
});
