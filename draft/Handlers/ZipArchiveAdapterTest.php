<?php

declare(strict_types=1);

namespace Esplora\Lumos\Tests;

use Esplora\Lumos\Adapters\ZipAdapter;
use Esplora\Lumos\Providers\ArrayPasswordProvider;
use PHPUnit\Framework\TestCase;

class ZipArchiveAdapterTest extends TestCase
{
    use Cleanup;

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
        $handler = new ZipAdapter;

        $result = $handler->extract(
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

        $handler = new ZipAdapter;

        $result = $handler->extract($archivePath, $this->getExtractionPath(), $this->getPasswords());

        $this->assertTrue($result);
        $this->assertFilesExtracted();
    }

    public function testExtractionFailureOnPassword(): void
    {
        $archivePath = $this->getFixturesDir('zip/protected.zip');

        $handler = new ZipAdapter;

        $result = $handler->extract($archivePath, $this->getExtractionPath(), new ArrayPasswordProvider([
            'wrongpassword',
        ]));

        $this->assertFalse($result);
        $this->assertFilesDoesExtracted();
    }
}
