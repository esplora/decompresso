<?php

declare(strict_types=1);

namespace Esplora\Decompresso\Concerns;

trait Always
{
    /**
     * Всегда возвращает true, независимо от переданного пути к файлу.
     *
     * @param string $filePath Путь к файлу, который нужно проверить.
     *
     * @return bool Всегда возвращает true.
     */
    public function canSupport(string $filePath): bool
    {
        return true;
    }
}
