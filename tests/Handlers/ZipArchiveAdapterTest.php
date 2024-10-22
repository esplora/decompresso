<?php

declare(strict_types=1);

namespace Esplora\Decompresso\Tests;

use Esplora\Decompresso\Adapters\ZipArchiveAdapter;
use Esplora\Decompresso\Extractor;
use Esplora\Decompresso\Providers\ArrayPasswordProvider;
use PHPUnit\Framework\TestCase;

class ZipArchiveAdapterTest extends TestCase
{
    use Cleanup;

    /**
     * Returns the expected list of files after extraction.
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
        $handler = new ZipArchiveAdapter;

        $result = $handler->extract(
            $this->getFixturesDir('zip/simple.zip'),
            $this->getExtractionPath(),
            $this->getPasswords()
        );

        $this->assertTrue($result);
        $this->assertFilesExtracted();
    }

    public function testExtractionSuccessWithPassword(): void
    {
        $archivePath = $this->getFixturesDir('zip/protected.zip');

        $handler = new ZipArchiveAdapter;

        $result = $handler->extract($archivePath, $this->getExtractionPath(), $this->getPasswords());

        $this->assertTrue($result);
        $this->assertFilesExtracted();
    }

    public function testExtractionFailureOnPassword(): void
    {
        $archivePath = $this->getFixturesDir('zip/protected.zip');

        $handler = new ZipArchiveAdapter;

        $result = $handler->extract($archivePath, $this->getExtractionPath(), new ArrayPasswordProvider([
            'wrongpassword',
        ]));

        $this->assertFalse($result);
        $this->assertFilesDoesExtracted();
    }

    /**
     * Тестирование рекурсивной распаковки архива с вложенными архивами до 30 уровней.
     */
    public function testRecursiveExtractionSuccess(): void
    {
        // Инициализация экстрактора с адаптером и уровнем рекурсии
        $extractor = (new Extractor)
            ->withAdapter(new ZipArchiveAdapter)
            ->recursive(30);  // Устанавливаем уровень рекурсии до 30

        // Выполняем распаковку
        $result = $extractor->extract(
            $this->getFixturesDir('zip/nested.zip'),
            $this->getExtractionPath()
        );

        // Проверяем, что распаковка прошла успешно
        $this->assertTrue($result);

        // Проверяем, что все файлы (включая вложенные архивы) были извлечены
        $this->assertFilesExtracted([
            'nested/simple.txt',
            'nested/inner/inner_file.txt',
            'nested/inner/deep/deep_file.txt',
        ]);
    }
}
