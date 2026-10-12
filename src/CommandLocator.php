<?php

declare(strict_types=1);

namespace CodeIgniter\Console;

use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

final class CommandLocator
{
    /**
     * Discover and instantiate Command classes from specified directories.
     *
     * Finds all *Command.php files under the given root directories, requires them,
     * and instantiates any Command subclasses using the provided factory callback.
     *
     * @param array<string> $roots Directories to search for *Command.php files
     * @param callable(class-string<Command>): Command $factory Factory callback to instantiate commands
     * @return array<Command> Discovered and instantiated command instances
     * @throws \InvalidArgumentException If factory returns a non-Command instance
     */
    public static function discover(array $roots, callable $factory): array
    {
        $files = [];

        foreach ($roots as $root) {
            if (!is_dir($root)) {
                continue;
            }

            $it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root));
            foreach ($it as $file) {
                if ($file->isFile() && str_ends_with($file->getFilename(), 'Command.php')) {
                    $files[] = $file->getPathname();
                }
            }
        }

        $classesBeforeLoad = get_declared_classes();
        $commands = [];
        $seenClasses = [];

        foreach (array_unique($files) as $file) {
            require_once $file;
        }

        // Only scan newly-loaded classes to improve performance
        $newClasses = array_diff(get_declared_classes(), $classesBeforeLoad);

        foreach ($newClasses as $class) {
            if (is_subclass_of($class, Command::class) && !isset($seenClasses[$class])) {
                $seenClasses[$class] = true;
                $command = $factory($class);

                if (!$command instanceof Command) {
                    throw new \InvalidArgumentException(
                        "Factory must return a Command instance for class '$class', got " . get_class($command)
                    );
                }

                $commands[] = $command;
            }
        }

        return $commands;
    }
}
