<?php

declare(strict_types=1);

namespace CodeIgniter\Console;

use CodeIgniter\Console\Output\Console;
use CodeIgniter\Console\Parse\Input;

/**
 * The main Console application orchestrator.
 */
final class App
{
    private string $version = '1.0-0';

    public function __construct(
        private string $name,
        private CommandRegistry $registry,
        private Console $console,
    ) {}

    /**
     * Runs the console application.
     * Parses input, resolves commands, and executes them.
     *
     * @param array $argv
     */
    public function run(array $argv)
    {
        $input = Input::fromArgv($argv);

        $this->renderHeader();

        // `ci` or `ci help` → show list
        if ($input->command() === null || $input->command() === 'help') {
            $listCommand = $this->registry->resolve('list');

            if ($listCommand) {
                $listCommand
                    ->withRegistry($this->registry)
                    ->run();
            }
            return 0;
        }

        $cmd = $this->registry->resolve($input->command());
        if (!$cmd) {
            $this->console->error("Unknown command: {$input->command()}");
            $this->console->line("Try: {$argv[0]} help");
            return 1;
        }

        try {
            return $cmd->handle($input);
        } catch (\Throwable $e) {
            $this->console->fatal($e);
            return 1;
        }
    }

    private function renderHeader(): void
    {
        $headerTitle = "{$this->name} Console (v{$this->version})";

        $this->console->line('');
        $this->console->line($headerTitle, $this->console->getThemeColor('info'));
        $this->console->line(str_repeat('=', strlen($headerTitle)), $this->console->getThemeColor('info'));
        $this->console->line('');
    }
}
