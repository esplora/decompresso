<?php

declare(strict_types=1);

namespace Esplora\Lumos\Tests;

use Esplora\Lumos\Adapters\MSOfficeCryptoToolAdapter;
use Esplora\Lumos\Adapters\ZipAdapter;
use Esplora\Lumos\Providers\ArrayPasswordProvider;
use PHPUnit\Framework\TestCase;

class OfficeCryptoToolAdapterTest extends TestCase
{
    use Cleanup;

    public function testExtractionSuccessWithPasswordPPTX(): void
    {
        $archivePath = $this->getFixturesDir('office-crypto/protected.pptx');

        $handler = new MSOfficeCryptoToolAdapter;

        $result = $handler->extract($archivePath, $this->getExtractionPath(), $this->getPasswords());

        $this->assertTrue($result);
        $this->assertFilesExtracted([
            'protected.pptx',
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
