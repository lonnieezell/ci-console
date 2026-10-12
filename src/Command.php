<?php

declare(strict_types=1);

namespace CodeIgniter\Console;

use CodeIgniter\Console\Output\Console;
use CodeIgniter\Console\Parse\Input;

abstract class Command
{
    const EXIT_SUCCESS = 0;
    const EXIT_FAILURE = 1;

    /**
     * The group the command is listed under.
     */
    public string $group = '';

    /**
     * The Command's name
     */
    public string $name = '';

    protected Input $input;
    protected CommandDefinition $definition;
    protected Console $console;

    public function __construct(Input $input, Console $console)
    {
        $this->input = $input;
        $this->definition = new CommandDefinition();
        $this->console = $console;
        $this->configure();
    }

    /**
     * Runs the command, validating input and executing the command logic.
     *
     * @return int Exit code
     */
    public function run(): int
    {
        if (!$this->validateInput()) {
            return self::EXIT_FAILURE;
        }

        return $this->execute();
    }

    public function showHelp()
    {
        // $console = Console::getInstance();
        // $console->displayHelpForCommand($this);
    }

    /**
     * Gets a string representation of the command's usage.
     * Builds a Unix-like style usage string from the command name and definition.
     */
    public function usage(): string
    {
        $usage = $this->name;

        // Add arguments to usage
        foreach ($this->definition->getArguments() as $argumentConfig) {
            $argName = $argumentConfig['name'];
            if ($argumentConfig['required']) {
                $usage .= " <{$argName}>";
            } else {
                $usage .= " [{$argName}]";
            }
        }

        // Add options to usage
        foreach ($this->definition->getOptions() as $optionConfig) {
            $optionName = $optionConfig['name'];
            $isRequired = $optionConfig['isFlag'] === false && !isset($optionConfig['default']);
            $isFlag = $optionConfig['isFlag'];

            $optionStr = $optionName;
            if (!$isFlag) {
                $optionStr .= '=';
            }

            if ($isRequired) {
                $usage .= " {{{$optionStr}}}";
            } else {
                $usage .= " {?{$optionStr}}";
            }
        }

        return $usage;
    }

    /**
     * This is where the command does its work.
     */
    abstract protected function execute();

    /**
     * Configure the command's definition.
     */
    protected function configure(): void {}

    /**
     * Validates the expected input against the provided input.
     */
    protected function validateInput(): bool
    {
        $errors = [];

        // Validate required arguments
        foreach ($this->definition->getArguments() as $argumentConfig) {
            $index = count($errors); // Use position in args array
            $name = $argumentConfig['name'];

            if ($argumentConfig['required'] && $this->input->arg($index) === null) {
                $errors[] = "Required argument '{$name}' is missing";
            }
        }

        // Validate required options
        foreach ($this->definition->getOptions() as $optionConfig) {
            $name = $optionConfig['name'];
            $shortcut = $optionConfig['shortcut'];

            if ($optionConfig['isFlag'] === false &&
                !$this->input->has($name) && !$this->input->has($shortcut)) {
                $errors[] = "Required option '--{$name}' (or '-{$shortcut}') is missing";
            }
        }

        if (!empty($errors)) {
            foreach ($errors as $error) {
                // TODO: Use console output
                echo "  " . $error . "\n";
            }
            return false;
        }

        return true;
    }

    public function __call($name, $arguments)
    {
        // Prevents magic method errors
    }
}
