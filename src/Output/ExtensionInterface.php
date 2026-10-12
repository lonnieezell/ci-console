<?php

declare(strict_types=1);

namespace CodeIgniter\Console\Output;

/**
 * Contract for console output extensions.
 */
interface ExtensionInterface
{
    /**
     * Get the extension name.
     * Should be unique and lowercase with underscores (e.g., 'table_formatter', 'spinner').
     *
     * @return string
     */
    public function getName(): string;

    /**
     * Execute the extension.
     */
    public function run(...$arguments): mixed;
}
