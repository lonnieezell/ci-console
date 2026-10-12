<?php

declare(strict_types=1);

namespace CodeIgniter\Console\Commands;

use CodeIgniter\Console\Command;
use CodeIgniter\Console\CommandRegistry;
use CodeIgniter\Console\Output\Console;
use CodeIgniter\Console\Parse\Input;

class ListCommand extends Command
{
    public string $name = 'list';
    public string $group = 'General';

    private CommandRegistry $registry;
    protected Console $console;

    public function __construct(Input $input, Console $console)
    {
        parent::__construct($input, $console);
        $this->console = $console;
    }

    public function withRegistry(CommandRegistry $registry): self
    {
        $this->registry = $registry;

        return $this;
    }

    protected function configure(): void
    {
        $this->definition->description('Lists all available commands');
    }

    protected function execute()
    {
        $commands = $this->registry->all();

        if (empty($commands)) {
            $this->console->line('No commands available.');
            return self::EXIT_SUCCESS;
        }
$this->console->clearScreen();
        // Group commands by their group property
        $grouped = [];
        $maxLength = 0;
        foreach ($commands as $command) {
            $group = $command->group ?? 'Other';
            if (!isset($grouped[$group])) {
                $grouped[$group] = [];
            }
            $grouped[$group][] = $command;

            // Collect the maximum command name length for formatting
            $maxLength = max($maxLength, strlen($command->name));
        }

        // Sort groups alphabetically
        ksort($grouped);

        // Display grouped commands
        foreach ($grouped as $group => $groupCommands) {
            $this->console->title($group);

            foreach ($groupCommands as $command) {
                $description = $command->definition->getDescription();

                // Name
                echo $this->console->write(
                    text: $this->console->fit("  {$command->name}", $maxLength + 6),
                    foreground: $this->console->getThemeColor('success'),
                    format: 'bold'
                );

                // Description
                echo $this->console->write($description . PHP_EOL);
            }

            $this->console->line('');
        }

        return self::EXIT_SUCCESS;
    }
}
