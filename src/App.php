<?php

declare(strict_types=1);

namespace CodeIgniter\Console;

use CodeIgniter\Console\Parse\Input;

final class App
{
    public function __construct(
        private string $name,
        private string $version,
        private CommandRegistry $registry,
        private Console $io
    ) {}

    public function run(array $argv): int
    {
        $input = Input::fromArgv($argv);

        // `ci` or `ci help` → show list
        if ($input->command() === null || $input->command() === 'help') {
            return $this->renderHelp($input);
        }

        $cmd = $this->registry->resolve($input->command());
        if (!$cmd) {
            $this->io->error("Unknown command: {$input->command()}");
            $this->io->line("Try: {$argv[0]} help");
            return 1;
        }

        try {
            return $cmd->handle($input);
        } catch (\Throwable $e) {
            $this->io->fatal($e);
            return 1;
        }
    }

    private function renderHelp(Input $input): int
    {
        $this->io->title("{$this->name} v{$this->version}");
        foreach ($this->registry->all() as $cmd) {
            $aliases = $cmd->aliases ? '('.implode(', ', $cmd->aliases).') ' : '';
            $this->io->writeln(sprintf("  <b>%-18s</b> %s", $cmd->name, $aliases.$cmd->description));
        }
        $this->io->line();
        $this->io->writeln('Use: ci <command> [args] [--options]');
        return 0;
    }
}
