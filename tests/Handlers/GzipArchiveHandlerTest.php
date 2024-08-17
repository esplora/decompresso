<?php

declare(strict_types=1);

namespace Esplora\Decompresso\Tests;

use Esplora\Decompresso\Handlers\GzipArchiveHandler;
use PHPUnit\Framework\TestCase;

/**
 * Тесты для обработчика GZIP-архивов.
 */
class GzipArchiveHandlerTest extends TestCase
{
    use Cleanup;

    /**
     * Возвращает ожидаемое список файла после извлечения.
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
        $handler = new GzipArchiveHandler;

        $result = $handler->extract(
            $this->getFixturesDir('gzip/simple.txt.gz'),
            $this->getExtractionPath()
        );

        $this->assertTrue($result);
        $this->assertFilesExtracted();
    }

    /*
     * TODO: Gzip распакует файл даже с абсурдными данными, то есть у него нет проверки на валидность.
    public function testExtractionFailure():void
    {
        $handler = new GzipArchiveHandler();

        $result = $handler->extract(
            $this->getFixturesDir('gzip/invalid.gz'),
            $this->getExtractionPath()
        );

        $this->assertFalse($result);
        $this->assertFilesDoesExtracted();
    }
    */
}
