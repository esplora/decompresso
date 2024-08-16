<?php

declare(strict_types=1);

namespace Esplora\Decompresso\Tests;

use Esplora\Decompresso\Handlers\ZipArchiveHandler;
use PHPUnit\Framework\TestCase;

/**
 * Тесты для обработчика ZIP-архивов.
 */
class ZipArchiveHandlerTest extends TestCase
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
        $handler = new ZipArchiveHandler;

        $result = $handler->extract(
            $this->getFixturesDir('zip/simple.zip'),
            $this->getExtractionPath()
        );

        $this->assertTrue($result);
        $this->assertFilesExtracted();
    }

    public function testExtractionSuccessWithPassword(): void
    {
        $archivePath = $this->getFixturesDir('zip/protected.zip');

        $handler = new ZipArchiveHandler;

        $result = $handler->extract($archivePath, $this->getExtractionPath(), $this->getPasswords());

        $this->assertTrue($result);
        $this->assertFilesExtracted();
    }

    public function testExtractionFailureOnPassword(): void
    {
        $archivePath = $this->getFixturesDir('zip/protected.zip');

        $handler = new ZipArchiveHandler;

        $result = $handler->extract($archivePath, $this->getExtractionPath(), [
            'wrongpassword',
        ]);

        $this->assertFalse($result);
        $this->assertFilesDoesExtracted();
    }
}
