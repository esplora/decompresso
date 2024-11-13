<?php

namespace Esplora\Lumos\Concerns;

trait DirectoryEnsurer
{
    /**
     * Ensures that the directory exists. Creates it if it does not.
     *
     * @param string $directory Path to the directory.
     *
     * @throws \RuntimeException If the directory could not be created.
     */
    protected function ensureDirectoryExists(string $directory): void
    {
        // Если директория не существует, создаем ее
        if (! is_dir($directory) && ! mkdir($directory, 0777, true) && ! is_dir($directory)) {
            throw new \RuntimeException("Failed to create directory: {$directory}");
        }
    }
}
