<?php

declare(strict_types=1);

namespace Esplora\Lumos\Tests;

use Esplora\Lumos\Adapters\MSOfficeCryptoToolAdapter;
use Esplora\Lumos\Contracts\AdapterInterface;
use Esplora\Lumos\Providers\ArrayPasswordProvider;

class MSOfficeCryptoToolAdapterTest extends AdapterTests
{
    /**
     * @return \Esplora\Lumos\Contracts\AdapterInterface
     */
    protected function adepter(): AdapterInterface
    {
        return new MSOfficeCryptoToolAdapter('/Users/tabuna/Library/Python/3.9/bin/msoffcrypto-tool');
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

        $this->assertTrue($result);
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

        $this->assertFalse($result);
    }
}
