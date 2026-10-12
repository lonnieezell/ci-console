<?php

declare(strict_types=1);

namespace CodeIgniter\Console;

/**
 * The CommandDefinition class provides a fluent interface for
 * defining console commands, including their name, description,
 * usage, and group.
 *
 * Example usage:
 * $definition = (new CommandDefinition())
 *     ->argument('filename', 'The name of the file to process', true)
 *     ->option('force', 'f', 'Force the operation', true)
 *     ->description('Processes a file with optional force flag.');
 */
final class CommandDefinition
{
    protected array $arguments = [];
    protected array $options = [];
    protected string $description = '';

    /**
     * Defines a single argument for the command.
     *
     * @param string $name          // i.e. 'filename'
     * @param string $description
     * @param bool $required        // true if argument is required
     * @param mixed $default        // default value if not required
     */
    public function argument(string $name, string $description = '', bool $required = false, $default = null): self
    {
        $this->arguments[] = [
            'name'        => $name,
            'description' => $description,
            'required'    => $required,
            'default'    => $default,
        ];

        return $this;
    }

    /**
     * Defines a single option or flag for the command.
     *
     * @param string $name          // i.e. 'force' for --force
     * @param string $shortcut      // i.e. 'f' for -f
     * @param string $description
     * @param bool $isFlag          // true if option is a flag (no value)
     * @param mixed $default        // default value if not a flag
     * @return CommandDefinition
    */
    public function option(string $name, string $shortcut, string $description = '', bool $isFlag = false, $default = null): self
    {
        $this->options[] = [
            'name'        => $name,
            'shortcut'    => $shortcut,
            'description' => $description,
            'isFlag'     => $isFlag,
            'default'    => $default,
        ];

        return $this;
    }

    /**
     * Sets the command's description.
     */
    public function description(string $description): self
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Gets the defined arguments.
     */
    public function getArguments(): array
    {
        return $this->arguments;
    }

    /**
     * Gets the defined options.
     */
    public function getOptions(): array
    {
        return $this->options;
    }

    /**
     * Gets the command's description.
     */
    public function getDescription(): string
    {
        return $this->description;
    }
}
