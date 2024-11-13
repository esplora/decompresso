<?php

namespace Esplora\Lumos\Tests;

use Esplora\Lumos\Contracts\AdapterInterface;
use Esplora\Lumos\Providers\ArrayPasswordProvider;
use PHPUnit\Framework\TestCase;

abstract class AdapterTests extends TestCase
{
    abstract protected function adepter(): AdapterInterface;

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
     * Returns a list of passwords for testing.
     *
     * @return ArrayPasswordProvider
     */
    protected function getPasswords(): ArrayPasswordProvider
    {
        return new ArrayPasswordProvider([
            '123',
            '12345',
            '123456',
            'Password1234_',
        ]);
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
        parent::setUp();
        $this->deleteDir($this->getExtractionPath());

        if (! $this->adepter()->isSupportedEnvironment()) {
            $this->markTestSkipped($this->adepter()::class.' is not supported.');
        }
    }

    protected function tearDown(): void
    {
        //  $this->deleteDir($this->getExtractionPath());
        parent::tearDown();
    }

    /**
     * Asserts that each file has been extracted.
     *
     * @param iterable $files
     *
     * @return void
     */
    protected function assertFilesExtracted(iterable $files = []): void
    {
        $files = empty($files) ? $this->getExpectedFiles() : $files;

        foreach ($files as $file) {
            $this->assertTrue(file_exists($this->getExtractionPath($file)));
        }
    }

    protected function assertFilesExtractedAndEquivalent(iterable $files = []): void
    {
        $files = empty($files) ? $this->getExpectedFiles() : $files;

        foreach ($files as $file) {
            $filePath = $this->getExtractionPath($file);

            $fileReferencePath = $this->getReferenceDir($file);

            $this->assertEquals(
                hash_file('sha1', $fileReferencePath),
                hash_file('sha1', $filePath),
                "File $file is corrupted or has been modified"
            );
        }
    }

    /**
     * Asserts that each file has not been extracted.
     *
     * @return void
     */
    protected function assertFilesDoesExtracted(): void
    {
        foreach ($this->getExpectedFiles() as $file) {
            $this->assertFileDoesNotExist($this->getExtractionPath($file));
        }
    }
}
