<?php

declare(strict_types=1);

namespace Esplora\Lumos\Tests;

use Esplora\Lumos\Adapters\SevenZipAdapter;
use Esplora\Lumos\Contracts\AdapterInterface;
use Esplora\Lumos\Providers\ArrayPasswordProvider;

class SevenZipAdapterTest extends AdapterTests
{
    /**
     * @return \Esplora\Lumos\Contracts\AdapterInterface
     */
    protected function adepter(): AdapterInterface
    {
        return new SevenZipAdapter(
            $_SERVER['SEVEN_ZIP_BIN_PATH']  ?? '7z'
        );
    }

    /**
     * Returns the expected list of files after extraction.
     *
     * @return array<string>
     */
    protected function getExpectedFiles(): array
    {
        return [
            'simple.txt',
        ];
    }

    public function testExtractionSuccess(): void
    {
        $result = $this->adepter()
            ->extract(
                $this->getFixturesDir('zip/simple.zip'),
                $this->getExtractionPath(),
                $this->getPasswords()
            );

        $this->assertTrue($result);
        $this->assertFilesExtracted();
    }

    public function testExtractionSuccessWithPassword(): void
    {
        $archivePath = $this->getFixturesDir('zip/protected.zip');

        $result = $this->adepter()
            ->extract(
                $archivePath,
                $this->getExtractionPath(),
                $this->getPasswords()
            );

        $this->assertTrue($result);
        $this->assertFilesExtracted();
    }

    public function testExtractionFailureOnPassword(): void
    {
        $archivePath = $this->getFixturesDir('zip/protected.zip');

        $result = $this->adepter()
            ->extract(
                $archivePath,
                $this->getExtractionPath(),
                new ArrayPasswordProvider([
                    'wrongpassword',
                ])
            );

        $this->assertFalse($result);
        // $this->assertFilesDoesExtracted();
    }
}
