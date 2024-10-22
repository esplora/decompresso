<?php

declare(strict_types=1);

namespace Esplora\Decompresso\Tests;

use Esplora\Decompresso\Adapters\GzipArchiveAdapter;
use Esplora\Decompresso\Adapters\TarArchiveAdapter;
use PHPUnit\Framework\TestCase;

class TarArchiveAdapterTest extends TestCase
{
    use Cleanup;

    public function testExtractionSuccess(): void
    {
        $handler = new TarArchiveAdapter;

        $result = $handler->extract(
            $this->getFixturesDir('tar/simple.txt.tar'),
            $this->getExtractionPath(),
            $this->getPasswords()
        );

        $this->assertTrue($result);
        $this->assertFilesExtracted([
            'simple.txt',
        ]);
    }

    /*
     * Gzip распакует файл даже с абсурдными данными, то есть у него нет проверки на валидность.
     **/
    public function testExtractionFailure(): void
    {
        $handler = new GzipArchiveAdapter;

        $result = $handler->extract(
            $this->getFixturesDir('gzip/invalid.gz'),
            $this->getExtractionPath(),
            $this->getPasswords()
        );

        $this->assertTrue($result);
        $this->assertFilesExtracted([
            'invalid',
        ]);
    }
}
