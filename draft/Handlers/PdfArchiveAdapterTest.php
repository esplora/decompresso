<?php

declare(strict_types=1);

namespace Esplora\Lumos\Tests;

use Esplora\Lumos\Adapters\QpdfAdapter;
use Esplora\Lumos\Adapters\ZipAdapter;
use Esplora\Lumos\Providers\ArrayPasswordProvider;
use PHPUnit\Framework\TestCase;

class PdfArchiveAdapterTest extends TestCase
{
    use Cleanup;

    public function testExtractionSuccess(): void
    {
        $handler = new QpdfAdapter;

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

        $handler = new QpdfAdapter;

        $result = $handler->extract($archivePath, $this->getExtractionPath(), $this->getPasswords());

        $this->assertTrue($result);
        $this->assertFilesExtracted([
            'protected.pdf',
        ]);
    }

    public function testExtractionFailureOnPassword(): void
    {
        $archivePath = $this->getFixturesDir('zip/protected.zip');

        $handler = new ZipAdapter;

        $result = $handler->extract($archivePath, $this->getExtractionPath(), new ArrayPasswordProvider([
            'wrongpassword',
        ]));

        $this->assertFalse($result);
    }
}
