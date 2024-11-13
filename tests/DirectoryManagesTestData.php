<?php

namespace Esplora\Lumos\Tests;


trait DirectoryManagesTestData
{
    /**
     * Returns the full path to the fixtures directory.
     *
     * @param string $path
     *
     * @return string
     */
    protected function getFixturesDir(string $path): string
    {
        return __DIR__.'/fixtures/'.$path;
    }

    /**
     * Returns the full path to the reference directory.
     *
     * @param string $path
     *
     * @return string
     */
    protected function getReferenceDir(string $path): string
    {
        return __DIR__.'/fixtures/reference/'.$path;
    }

    /**
     * Returns the full path to the extraction directory.
     *
     * @param string $path
     *
     * @return string
     */
    protected function getExtractionPath(string $path = ''): string
    {
        return __DIR__.'/extracted/'.$path;
    }

    /**
     * Recursively deletes a directory and its contents.
     *
     * @param string $dir Directory to delete.
     */
    private function deleteDir(string $dir): void
    {
        if (! is_dir($dir)) {
            return;
        }

        $items = array_diff(scandir($dir), ['.', '..']);
        foreach ($items as $item) {
            $path = $dir.DIRECTORY_SEPARATOR.$item;
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
        $this->deleteDir($this->getExtractionPath());
        parent::setUp();
    }
}
