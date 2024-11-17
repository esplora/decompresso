<?php

declare(strict_types=1);

namespace Esplora\Lumos\Tests;

use Esplora\Lumos\Adapters\MSOfficeCryptoToolAdapter;
use Esplora\Lumos\Contracts\AdapterInterface;
use Esplora\Lumos\Providers\ArrayPasswordProvider;

class MSOfficeCryptoToolAdapterTest extends AdapterTests
{
    protected function adepter(): AdapterInterface
    {
        return new MSOfficeCryptoToolAdapter(
            $_SERVER['MSOFFCRYPTO_TOOL_BIN_PATH'] ?? 'msoffcrypto-tool'
        );
    }

    public function testExtractionSimpleDocSuccess(): void
    {
        $result = $this->adepter()
            ->extract(
                $this->getFixturesDir('office-crypto/simple.doc'),
                $this->getExtractionPath(),
                $this->getPasswords()
            );

        $this->assertTrue($result->isSuccessful());
        $this->assertFilesExtracted([
            'simple.doc',
        ]);
    }

    public function testExtractionSuccessWithPasswordPPT(): void
    {
        $archivePath = $this->getFixturesDir('office-crypto/protected.ppt');

        $result = $this->adepter()
            ->extract(
                $archivePath,
                $this->getExtractionPath(),
                $this->getPasswords()
            );

        $this->assertTrue($result->isSuccessful());
        $this->assertFilesExtracted([
            'protected.ppt',
        ]);
    }

    public function testExtractionFailureOnPassword(): void
    {
        $archivePath = $this->getFixturesDir('office-crypto/protected.ppt');

        $result = $this->adepter()->extract($archivePath, $this->getExtractionPath(), new ArrayPasswordProvider([
            'wrongpassword',
        ]));

        $this->assertFalse($result->isSuccessful());
    }
}
