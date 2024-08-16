<?php

declare(strict_types=1);

namespace Esplora\Decompresso\Tests;

use Esplora\Decompresso\Contracts\ArchiveInterface;
use Esplora\Decompresso\Contracts\PasswordProviderInterface;
use Esplora\Decompresso\Extractor;
use PHPUnit\Framework\TestCase;

class ExtractorTest extends TestCase
{
    /*
    public function testExtractorWithOutPassword()
    {
        $extractor = (new Extractor())
            ->withPasswords(new SimplePasswordProvider(['123', 'xxx123']))
            ->withHandler(new ZipArchiveHandler())
            ->onFailure(function ($filePath) {
                echo "Не удалось извлечь архив: $filePath";
            })
            ->onSuccess(function ($filePath) {
                echo "Архив успешно извлечён: $filePath";
            });

        $extractor->extract('/path/to/your/archive.zip');
    }
    */

    /**
     * @var Extractor
     */
    private Extractor $extractor;

    protected function setUp(): void
    {
        parent::setUp();
        $this->extractor = new Extractor;
    }

    public function testExtractionSuccess(): void
    {
        $passwordProvider = $this->createMock(PasswordProviderInterface::class);
        $passwordProvider->method('getPasswords')->willReturn(['123', 'xxx123']);

        $archiveHandler = $this->createMock(ArchiveInterface::class);
        $archiveHandler->method('extract')
            ->willReturn(true);

        $this->extractor->withPasswords($passwordProvider)
            ->withHandler($archiveHandler)
            ->onSuccess(fn ($filePath) => $filePath.' extracted successfully.')
            ->onFailure(fn ($filePath) => 'Failed to extract '.$filePath);

        $result = $this->extractor->extract('/path/to/archive.zip');

        $this->assertEquals('/path/to/archive.zip extracted successfully.', $result);
    }

    public function testExtractionFailure(): void
    {
        $passwordProvider = $this->createMock(PasswordProviderInterface::class);
        $passwordProvider->method('getPasswords')->willReturn(['123', 'xxx123']);

        $archiveHandler = $this->createMock(ArchiveInterface::class);
        $archiveHandler->method('extract')
            ->willReturn(false); // Симулируем неудачное извлечение

        // Устанавливаем обработчик, который выбрасывает исключение при неудаче
        $this->extractor->withPasswords($passwordProvider)
            ->withHandler($archiveHandler)
            ->onFailure(fn ($filePath) => throw new \Exception("Не удалось извлечь архив: {$filePath}"));

        // Ожидаем, что будет выброшено исключение
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Не удалось извлечь архив: /path/to/archive.zip');

        $this->extractor->extract('/path/to/archive.zip');
    }
}
