<?php

declare(strict_types=1);

namespace CodeIgniter\Console;

/**
 * The command registry maintains a list of available console commands.
 */
final class CommandRegistry
{
    /** @var array<string, Command> */
    private array $map = [];

    /**
     * Registers a command.
     */
    public function register(Command $command): void
    {
        $this->map[$command->name] = $command;
    }

    /**
     * Resolves a command by name or alias.
     */
    public function resolve(?string $name): ?Command
    {
        return $name
            ? ($this->map[$name] ?? null)
            : null;
    }

    /**
     * Returns all registered commands.
     *
     * @return Command[]
     */
    public function all(): array
    {
        // de-duplicate aliases
        $seen = [];
        $unique = [];

        foreach ($this->map as $n => $c) {
            $oid = spl_object_id($c);

            if (isset($seen[$oid])) {
                continue;
            }

            $seen[$oid] = true;
            $unique[] = $c;
        }

        usort($unique, fn($a, $b) => strcmp($a->name, $b->name));

        return $unique;
    }
}
