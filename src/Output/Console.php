<?php

declare(strict_types=1);

namespace CodeIgniter\Console\Output;

use PhpParser\Node\Expr;

/**
 * Provides methods for outputting to the console
 * such as writing text, formatting, and colors.
 * Supports a color theme system (see Theme class).
 *
 * Can be extended to add more functionality.
 */
class Console
{
    private ExtensionRegistry $extensionRegistry;

    public function __construct(
        public string $theme = 'default',
        protected string $environment = 'production')
    {
    }

    public function withExtensionRegistry(ExtensionRegistry $registry): self
    {
        $this->extensionRegistry = $registry;

        return $this;
    }

    /**
     * Writes text to the console with optional foreground and background colors.
     * Multiple writes can be called in succession to build complex output on a single line.
     *
     * Supported formats: bold, italic, underline, blink, reverse, strikethrough
     */
    public function write(string $text, string $foreground = '', string $background = '', ?string $format = null): string
    {
        $codes = [];

        $theme = Theme::get($this->theme);
        $codes[] = $this->getColorCode($foreground ?: ($theme['text'] ?? 'white'));

        if ($background !== '') {
            $codes[] = $this->getColorCode($background, true);
        }

        switch($format) {
            case 'bold':
                $codes[] = '1';
                break;
            case 'italic':
                $codes[] = '3';
                break;
            case 'underline':
                $codes[] = '4';
                break;
            case 'blink':
                $codes[] = '5';
                break;
            case 'reverse':
                $codes[] = '7';
                break;
            case 'strikethrough':
                $codes[] = '9';
                break;
        }

        if ($codes === []) {
            return $text;
        }

        return "\033[" . implode(';', $codes) . "m{$text}\033[0m";
    }

    /**
     * Outputs a line of text to the console, appending a newline.
     */
    public function line(string $text = '', string $foreground = '', string $background = '', ?string $format = null): void
    {
        echo $this->write($text, $foreground, $background, $format) . "\n";
    }

    /**
     * Get a color value from the current theme.
     *
     * @param string $key The color key (success, error, warning, info, text, title)
     * @param string $default The default color if key not found
     * @return string The color name
     */
    public function getThemeColor(string $key, string $default = 'white'): string
    {
        try {
            $theme = Theme::get($this->theme);
            return $theme[$key] ?? $default;
        } catch (\InvalidArgumentException) {
            return $default;
        }
    }

    /**
     * Outputs a success message to the console.
     */
    public function success(string $text): void
    {
        echo $this->write("✓ {$text}", $this->getThemeColor('success', 'brightgreen')) . "\n";
    }

    /**
     * Outputs a warning message to the console.
     */
    public function warning(string $text): void
    {
        echo $this->write("⚠ {$text}", $this->getThemeColor('warning', 'brightyellow')) . "\n";
    }

    /**
     * Outputs an error message to the console.
     */
    public function error(string $text): void
    {
        echo $this->write("✗ {$text}", $this->getThemeColor('error', 'red')) . "\n";
    }

    /**
     * Outputs an informational message to the console.
     */
    public function info(string $text): void
    {
        echo $this->write("ℹ {$text}", $this->getThemeColor('info', 'brightblue')) . "\n";
    }

    /**
     * Outputs a debug message to the console.
     */
    public function debug(string $text): void
    {
        if ($this->environment !== 'development') {
            return;
        }

        echo $this->write("🐛 {$text}", 'purple') . "\n";
    }

    /**
     * Outputs a title message to the console.
     */
    public function title(string $text): void
    {
        echo $this->write("{$text}", $this->getThemeColor('title', 'aqua'), '', 'bold') . "\n";
    }

    /**
     * Outputs a fatal error message to the console with stack trace.
     */
    public function fatal(\Throwable $e): void
    {
        echo $this->write("💥 Fatal Error: {$e->getMessage()}", $this->getThemeColor('error', 'red'), '', 'bold') . "\n";
        echo $this->write($e->getTraceAsString(), 'red') . "\n";
    }

    /**
     * Outputs raw text to the console without any formatting or colors.
     * Useful for outputting plain text that shouldn't be colored.
     */
    public function raw(string $text): void
    {
        echo $text;
    }

    /**
     * Fits text into a given width by padding with spaces.
     *
     * Example:
     *    echo $console->fit('Hello', 10); // Outputs 'Hello     '
     */
    public function fit(string $text, int $in, string $pad = ' '): string
    {
        $len = strlen($text);

        if ($len >= $in) {
            return $text;
        }

        return str_pad($text, $in, $pad);
    }

    /**
     * Clears the console screen.
     *
     * Note: If running on older Windows without ANSI support,
     * the output will be invisible but won't cause errors
     */
    public function clearScreen(): void
    {
        $this->isWindows() && ! static::streamSupports('sapi_windows_vt100_support', STDOUT)
            ? static::newLine(40)
            : static::fwrite(STDOUT, "\033[H\033[2J");
    }

    /**
     * Determine if the current OS is Windows.
     */
    public function isWindows(): bool
    {
        return strtoupper(substr(PHP_OS, 0, 3)) === 'WIN';
    }

    /**
     * Set the active theme.
     *
     * @param string $name The theme name
     * @return self Returns self for method chaining
     * @throws \InvalidArgumentException If theme does not exist
     */
    public function setTheme(string $name): self
    {
        if (!Theme::exists($name)) {
            throw new \InvalidArgumentException("Theme '{$name}' does not exist");
        }
        $this->theme = $name;
        return $this;
    }

    /**
     * Check if output should be shown based on environment restrictions.
     * Can be used by callers to conditionally output based on current environment.
     *
     * @return bool True if in development environment
     */
    public function isDevelopment(): bool
    {
        return $this->environment === 'development';
    }

    /**
     * Gets the ANSI color code for a given color name or number.
     *
     * @param string $color The color name or number (0-255).
     * @param bool $isBackground True for background color, false for foreground.
     *
     * @return string The ANSI color code.
     */
    protected function getColorCode(string $color,  bool $isBackground = false): string
    {
        $prefix = $isBackground ? '48;5;' : '38;5;';

        // Check if it's a named color in the theme palette
        $colorCode = Theme::getColor($color);
        if ($colorCode !== null) {
            return $prefix . $colorCode;
        }

        // Check if it's a direct color number
        if (is_numeric($color) && $color >= 0 && $color <= 255) {
            return $prefix . $color;
        }

        // Default to white if color not found
        return $prefix . '15';
    }

    /**
     * Magic method to handle calling dynamic extensions.
     */
    public function __call(string $method, array $args)
    {
        if (! $this->extensionRegistry instanceof ExtensionRegistry) {
            $this->extensionRegistry = new ExtensionRegistry();
            $this->extensionRegistry->loadAll();
        }

        if ($this->extensionRegistry->has($method)) {
            $extension = $this->extensionRegistry->get($method);

            if ($extension) {
                array_unshift($args, $this);
                return $extension->run(...$args);
            }
        }

        throw new \BadMethodCallException("Method '{$method}' does not exist on Console class or any registered extensions.");
    }
}
