<?php

declare(strict_types=1);

namespace Esplora\Decompresso\Tests;

use Esplora\Decompresso\Adapters\LibreOfficeAdapter;
use Esplora\Decompresso\Adapters\PdfArchiveAdapter;
use Esplora\Decompresso\Adapters\ZipArchiveAdapter;
use Esplora\Decompresso\Providers\ArrayPasswordProvider;
use PHPUnit\Framework\TestCase;

class LibreOfficeAdapterTest extends TestCase
{
    use Cleanup;

    public function testExtractionSuccessWithPasswordPPTX(): void
    {
        $archivePath = $this->getFixturesDir('libre-office/protected.pptx');

        $handler = new LibreOfficeAdapter();

        $result = $handler->extract($archivePath, $this->getExtractionPath(), $this->getPasswords());

        $this->assertTrue($result);
        $this->assertFilesExtracted([
            'protected.pptx',
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
