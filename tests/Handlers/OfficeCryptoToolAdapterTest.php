<?php

declare(strict_types=1);

namespace Esplora\Decompresso\Tests;

use Esplora\Decompresso\Adapters\OfficeCryptoToolAdapter;
use Esplora\Decompresso\Adapters\ZipArchiveAdapter;
use Esplora\Decompresso\Providers\ArrayPasswordProvider;
use PHPUnit\Framework\TestCase;

class OfficeCryptoToolAdapterTest extends TestCase
{
    use Cleanup;

    public function testExtractionSuccessWithPasswordPPTX(): void
    {
        $archivePath = $this->getFixturesDir('office-crypto/protected.pptx');

        $handler = new OfficeCryptoToolAdapter;

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
