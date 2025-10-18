<?php

declare(strict_types=1);

namespace CodeIgniter\Console;

use CodeIgniter\Console\IO\Console;
use CodeIgniter\Console\Parse\Input;

abstract class Command
{
    public string $name = '';
    public string $description = '';
    public string $usage = '';            // e.g. "foo {arg} {--opt=} {--flag}"
    public array  $aliases = [];

    protected Console $io;

    final public function __construct(Console $io) { $this->io = $io; }

    /** Describe your arguments/options for help + validation. */
    public function signature(): string { return $this->usage; }

    /** Validate + run. Return exit code (0 success). */
    abstract public function handle(Input $input): int;

    /** Optional hook */
    public function configure(): void {}
}
