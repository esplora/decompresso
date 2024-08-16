<?php

namespace Tabuna\Archive\Tests;

/**
 * Трейт для выполнения операций с директориями в тестах.
 */
trait Cleanup
{
    /**
     * Возвращает полный путь к директории фиктивных файлов.
     *
     * @return string
     */
    protected function getFixturesDir(): string
    {
        return __DIR__ . '/../fixtures/zip/';
    }

    /**
     * Возвращает полный путь к директории извлечения.
     *
     * @return string
     */
    protected function getExtractionPath(): string
    {
        return __DIR__ . '/../extracted/';
    }

    /**
     * Удаляет каталог и все его содержимое рекурсивно.
     *
     * @param string $dir Каталог для удаления.
     */
    private function deleteDir(string $dir): void
    {
        if (!is_dir($dir)) {
            return;
        }

        $items = array_diff(scandir($dir), ['.', '..']);
        foreach ($items as $item) {
            $path = $dir . DIRECTORY_SEPARATOR . $item;
            if (is_dir($path)) {
                $this->deleteDir($path);
            } else {
                unlink($path);
            }
        }
        rmdir($dir);
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->deleteDir($this->getExtractionPath());
    }

    protected function tearDown(): void
    {
        $this->deleteDir($this->getExtractionPath());
        parent::tearDown();
    }
}
