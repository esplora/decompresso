<?php

declare(strict_types=1);

namespace Esplora\Decompresso\Tests;

use Esplora\Decompresso\Adapters\PdfArchiveAdapter;
use Esplora\Decompresso\Adapters\ZipArchiveAdapter;
use Esplora\Decompresso\Providers\ArrayPasswordProvider;
use PHPUnit\Framework\TestCase;

class PdfArchiveAdapterTest extends TestCase
{
    use Cleanup;

    public function testExtractionSuccess(): void
    {
        $handler = new PdfArchiveAdapter;

        $result = $handler->extract(
            $this->getFixturesDir('pdf/simple.pdf'),
            $this->getExtractionPath(),
            $this->getPasswords()
        );

        $this->assertTrue($result);
        $this->assertFilesExtracted([
            'simple.pdf',
        ]);
    }

    public function testPdfExtractionSuccessWithPassword(): void
    {
        $archivePath = $this->getFixturesDir('pdf/protected.pdf');

        $handler = new PdfArchiveAdapter;

        $result = $handler->extract($archivePath, $this->getExtractionPath(), $this->getPasswords());

        $this->assertTrue($result);
        $this->assertFilesExtracted([
            'protected.pdf',
        ]);
    }

    public function testExtractionFailureOnPassword(): void
    {
        $archivePath = $this->getFixturesDir('zip/protected.zip');

        $handler = new ZipArchiveAdapter;

        $result = $handler->extract($archivePath, $this->getExtractionPath(), new ArrayPasswordProvider([
            'wrongpassword',
        ]));

        $this->assertFalse($result);
    }
}
