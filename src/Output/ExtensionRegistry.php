<?php

declare(strict_types=1);

namespace CodeIgniter\Console\Output;

/**
 * Manages console output extensions.
 * Extensions are classes that provide additional console functionality
 * and follow the ExtensionInterface contract.
 */
class ExtensionRegistry
{
    /**
     * @var array<string, ExtensionInterface> Loaded extensions
     */
    protected array $extensions = [];

    /**
     * @var array<string> Paths to search for extensions
     */
    protected array $extensionPaths = [];

    public function __construct()
    {
        // Default extension paths
        $this->extensionPaths = [
            __DIR__ . '/Extensions',
        ];
    }

    /**
     * Register an extension instance.
     *
     * @param ExtensionInterface $extension
     * @return self
     */
    public function register(ExtensionInterface $extension): self
    {
        $this->extensions[$extension->getName()] = $extension;
        return $this;
    }

    /**
     * Load all available extensions from registered paths.
     *
     * @return self
     */
    public function loadAll(): self
    {
        foreach ($this->extensionPaths as $path) {
            if (!is_dir($path)) {
                continue;
            }

            $files = scandir($path);
            foreach ($files as $file) {
                if ($file === '.' || $file === '..' || !str_ends_with($file, '.php')) {
                    continue;
                }

                $this->loadExtensionFile($path . '/' . $file);
            }
        }

        return $this;
    }

    /**
     * Load a single extension file.
     *
     * @param string $filePath
     * @return void
     */
    protected function loadExtensionFile(string $filePath): void
    {
        try {
            require_once $filePath;

            // Extract class name from file path
            $className = basename($filePath, '.php');
            $fullyQualified = "CodeIgniter\\Console\\Extensions\\{$className}";

            if (class_exists($fullyQualified) && is_subclass_of($fullyQualified, ExtensionInterface::class)) {
                $extension = new $fullyQualified();
                $this->register($extension);
            }
        } catch (\Throwable) {
            // Silently skip invalid extensions
        }
    }

    /**
     * Add a path to search for extensions.
     *
     * @param string $path
     * @return self
     */
    public function addPath(string $path): self
    {
        if (!in_array($path, $this->extensionPaths, true)) {
            $this->extensionPaths[] = $path;
        }
        return $this;
    }

    /**
     * Get an extension by name.
     *
     * @param string $name
     * @return ExtensionInterface|null
     */
    public function get(string $name): ?ExtensionInterface
    {
        return $this->extensions[$name] ?? null;
    }

    /**
     * Check if an extension exists.
     *
     * @param string $name
     * @return bool
     */
    public function has(string $name): bool
    {
        return isset($this->extensions[$name]);
    }

    /**
     * Get all registered extensions.
     *
     * @return array<string, ExtensionInterface>
     */
    public function all(): array
    {
        return $this->extensions;
    }
}
