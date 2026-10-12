<?php

declare(strict_types=1);

namespace CodeIgniter\Console\Output;

/**
 * Theme class for managing console output themes.
 *
 * Provides a way to define and register custom themes for console output.
 * Each theme is an array of color mappings for different message types.
 *
 * Example of registering a custom theme:
 * Theme::register('my-theme', [
 *     'success' => 'lime',
 *     'error'   => 'red',
 *     'warning' => 'yellow',
 *     'info'    => 'blue',
 *     'text'    => 'white',
 *     'title'   => 'aqua',
 * ]);
 */
final class Theme
{
    /**
     * 8-bit color palette (256 colors)
     */
    protected static array $palette = [
        // Standard colors (0-15)
        'black' => 0, 'maroon' => 1, 'green' => 2, 'olive' => 3,
        'navy' => 4, 'purple' => 5, 'teal' => 6, 'silver' => 7,
        'grey' => 8, 'red' => 9, 'lime' => 10, 'yellow' => 11,
        'blue' => 12, 'fuchsia' => 13, 'aqua' => 14, 'white' => 15,

        // Extended colors (16-231) - organized by hue
        // Reds & Pinks
        'orange' => 208, 'darkorange' => 166, 'coral' => 203, 'salmon' => 209,
        'crimson' => 160, 'pink' => 205, 'lightpink' => 217, 'hotpink' => 198,

        // Greens
        'darkgreen' => 22, 'lightgreen' => 120, 'mediumgreen' => 35, 'emerald' => 29,
        'seagreen' => 72, 'springgreen' => 48, 'brightgreen' => 82,

        // Blues & Cyans
        'darkblue' => 18, 'lightblue' => 117, 'steelblue' => 67, 'slateblue' => 62,
        'dodgerblue' => 33, 'royalblue' => 63, 'skyblue' => 111, 'lightcyan' => 195,
        'brightblue' => 51,

        // Yellows & Golds
        'gold' => 220, 'khaki' => 185, 'lightyellow' => 227, 'yellowgreen' => 154,
        'brightyellow' => 226,

        // Purples & Violets
        'violet' => 99, 'indigo' => 54, 'mediumpurple' => 141, 'plum' => 182,
        'orchid' => 170, 'thistle' => 225,

        // Browns & Grays
        'brown' => 94, 'chocolate' => 130, 'tan' => 180, 'wheat' => 223,
        'darkgray' => 59, 'lightgray' => 188,
    ];

    /**
     * Built-in themes
     */
    protected static array $themes = [
        'default' => [
            'success' => 'lime',
            'error'   => 'red',
            'warning' => 'yellow',
            'info'    => 'blue',
            'text'    => 'white',
            'title'   => 'aqua',
        ],
        'monochromatic' => [
            'success' => 'silver',
            'error'   => 'grey',
            'warning' => 'white',
            'info'    => 'silver',
            'text'    => 'silver',
            'title'   => 'white',
        ],
        'high-contrast' => [
            'success' => 'lime',
            'error'   => 'red',
            'warning' => 'gold',
            'info'    => 'brightblue',
            'text'    => 'white',
            'title'   => 'lime',
        ],
        'pastel' => [
            'success' => 'lightgreen',
            'error'   => 'lightpink',
            'warning' => 'lightyellow',
            'info'    => 'lightblue',
            'text'    => 'white',
            'title'   => 'lightblue',
        ],
        'amber' => [
            'success' => 'lime',
            'error'   => 'orange',
            'warning' => 'yellow',
            'info'    => 'gold',
            'text'    => 'white',
            'title'   => 'gold',
        ],
        'forest' => [
            'success' => 'lightgreen',
            'error'   => 'crimson',
            'warning' => 'gold',
            'info'    => 'teal',
            'text'    => 'silver',
            'title'   => 'springgreen',
        ],
        'ocean' => [
            'success' => 'aqua',
            'error'   => 'red',
            'warning' => 'gold',
            'info'    => 'blue',
            'text'    => 'white',
            'title'   => 'skyblue',
        ],
        'sunset' => [
            'success' => 'lime',
            'error'   => 'darkorange',
            'warning' => 'orange',
            'info'    => 'coral',
            'text'    => 'white',
            'title'   => 'coral',
        ],
    ];

    /**
     * Get a theme by name.
     *
     * @param string $name The theme name
     * @return array The theme colors (copy, not reference)
     * @throws \InvalidArgumentException If theme does not exist
     */
    public static function get(string $name): array
    {
        if (!isset(self::$themes[$name])) {
            throw new \InvalidArgumentException("Theme '{$name}' does not exist");
        }

        return self::$themes[$name];
    }

    /**
     * Get a color code from the palette by name.
     *
     * @param string $color The color name
     * @return int|null The color code (0-255), or null if not found
     */
    public static function getColor(string $color): ?int
    {
        return self::$palette[$color] ?? null;
    }

    /**
     * Register a new color in the palette.
     *
     * @param string $name The color name
     * @param int $code The color code (0-255)
     * @return void
     */
    public static function registerColor(string $name, int $code): void
    {
        if ($code < 0 || $code > 255) {
            throw new \InvalidArgumentException("Color code must be between 0 and 255, got {$code}");
        }
        self::$palette[$name] = $code;
    }

    /**
     * Check if a color exists in the palette.
     *
     * @param string $color The color name
     * @return bool True if color exists
     */
    public static function hasColor(string $color): bool
    {
        return isset(self::$palette[$color]);
    }

    /**
     * Get all colors in the palette.
     *
     * @return array The color palette (copy, not reference)
     */
    public static function getPalette(): array
    {
        return self::$palette;
    }

    /**
     * Register multiple colors in the palette.
     *
     * @param array $colors Array of color name => code pairs
     * @return void
     */
    public static function registerPalette(array $colors): void
    {
        foreach ($colors as $name => $code) {
            self::registerColor($name, $code);
        }
    }

    /**
     * Register a new theme.
     *
     * @param string $name The theme name
     * @param array $colors The color mappings
     * @param bool $validate Whether to validate that all colors exist in the palette
     * @return void
     * @throws \InvalidArgumentException If validation is enabled and colors don't exist
     */
    public static function register(string $name, array $colors, bool $validate = true): void
    {
        if ($validate) {
            self::validateTheme($colors);
        }
        self::$themes[$name] = $colors;
    }

    /**
     * Check if a theme exists.
     *
     * @param string $name The theme name
     * @return bool True if theme exists
     */
    public static function exists(string $name): bool
    {
        return isset(self::$themes[$name]);
    }

    /**
     * Get all available theme names.
     *
     * @return array List of theme names
     */
    public static function list(): array
    {
        return array_keys(self::$themes);
    }

    /**
     * Get all themes.
     *
     * @return array All themes
     */
    public static function all(): array
    {
        return self::$themes;
    }

    /**
     * Unregister (delete) a theme.
     *
     * @param string $name The theme name
     * @return bool True if theme was removed, false if it didn't exist
     */
    public static function unregister(string $name): bool
    {
        if (isset(self::$themes[$name])) {
            unset(self::$themes[$name]);
            return true;
        }
        return false;
    }

    /**
     * Unregister (delete) a color from the palette.
     *
     * @param string $name The color name
     * @return bool True if color was removed, false if it didn't exist
     */
    public static function unregisterColor(string $name): bool
    {
        if (isset(self::$palette[$name])) {
            unset(self::$palette[$name]);
            return true;
        }
        return false;
    }

    /**
     * Validate that all colors in a theme exist in the palette.
     *
     * @param array $theme The theme color mappings
     * @return void
     * @throws \InvalidArgumentException If any color in the theme doesn't exist in the palette
     */
    public static function validateTheme(array $theme): void
    {
        $missingColors = [];
        foreach ($theme as $colorName) {
            if (!self::hasColor($colorName)) {
                $missingColors[] = $colorName;
            }
        }

        if (!empty($missingColors)) {
            throw new \InvalidArgumentException(
                "Theme references non-existent colors: " . implode(', ', $missingColors)
            );
        }
    }

    /**
     * Update (modify) specific colors in an existing theme.
     *
     * @param string $name The theme name
     * @param array $colorUpdates Array of color key => color name pairs to update
     * @param bool $validate Whether to validate that updated colors exist in the palette
     * @return void
     * @throws \InvalidArgumentException If theme doesn't exist or validation fails
     */
    public static function updateTheme(string $name, array $colorUpdates, bool $validate = true): void
    {
        if (!self::exists($name)) {
            throw new \InvalidArgumentException("Theme '{$name}' does not exist");
        }

        if ($validate) {
            self::validateTheme($colorUpdates);
        }

        self::$themes[$name] = array_merge(self::$themes[$name], $colorUpdates);
    }
}
