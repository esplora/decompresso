<?php

declare(strict_types=1);

namespace Esplora\Lumos\Tests;

use Esplora\Lumos\Adapters\QpdfAdapter;
use Esplora\Lumos\Contracts\AdapterInterface;
use Esplora\Lumos\Providers\ArrayPasswordProvider;

class QpdfAdapterTest extends AdapterTests
{
    /**
     * @return \Esplora\Lumos\Contracts\AdapterInterface
     */
    protected function adepter(): AdapterInterface
    {
        return new QpdfAdapter();
    }

    public function testExtractionSuccess(): void
    {
        $result = $this->adepter()
            ->extract(
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

        $result = $this->adepter()
            ->extract($archivePath, $this->getExtractionPath(), $this->getPasswords());

        $this->assertTrue($result);
        $this->assertFilesExtracted([
            'protected.pdf',
        ]);
    }

    public function testExtractionFailureOnPassword(): void
    {
        $archivePath = $this->getFixturesDir('pdf/protected.pdf');

        $result = $this->adepter()->extract($archivePath, $this->getExtractionPath(), new ArrayPasswordProvider([
            'wrongpassword',
        ]));

        $this->assertFalse($result);
    }
}
