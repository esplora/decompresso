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

        $archiveHandler->method('canSupport')
            ->willReturn(true);

        $this->extractor->withPasswords($passwordProvider)
            ->withHandler($archiveHandler)
            ->onSuccess(fn ($filePath) => $filePath.' extracted successfully.')
            ->onFailure(fn ($filePath) => 'Failed to extract '.$filePath);

        $result = $this->extractor->extract('/path/to/archive.zip');

        $this->assertEquals('/path/to/archive.zip extracted successfully.', $result);
    }

    public function testExtractionPasswordFailure(): void
    {
        $passwordProvider = $this->createMock(PasswordProviderInterface::class);
        $passwordProvider->method('getPasswords')->willReturn(['123', 'xxx123']);

        $archiveHandler = $this->createMock(ArchiveInterface::class);
        $archiveHandler->method('extract')
            ->willReturn(false); // Симулируем неудачное извлечение по паролю

        // Устанавливаем обработчик, который выбрасывает исключение при неудаче
        $this->extractor->withPasswords($passwordProvider)
            ->withHandler($archiveHandler)
            ->onPasswordFailure(fn ($filePath) => throw new \Exception("Не удалось извлечь архив: {$filePath}"));

        // Ожидаем, что будет выброшено исключение
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Не удалось извлечь архив: /path/to/archive.zip');

        $this->extractor->extract('/path/to/archive.zip');
    }

    public function testExtractionExceptionFailure(): void
    {
        $passwordProvider = $this->createMock(PasswordProviderInterface::class);
        $passwordProvider->method('getPasswords')->willReturn(['123', 'xxx123']);

        $archiveHandler = $this->createMock(ArchiveInterface::class);
        $archiveHandler->method('canSupport')
            ->willReturn(true);

        $archiveHandler->method('extract')
            ->willThrowException(new \RuntimeException('Failed to extract archive.'));

        // Устанавливаем обработчик, который выбрасывает исключение при неудаче
        $this->extractor->withPasswords($passwordProvider)
            ->withHandler($archiveHandler)
            ->onFailure(fn ($e) => throw new \Exception('New: '.$e->getMessage()));

        // Ожидаем, что будет выброшено исключение
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('New: Failed to extract archive.');

        $this->extractor->extract('/path/to/archive.zip');
    }

    public function testExtractionWiouSuccess(): void
    {
        $passwordProvider = $this->createMock(PasswordProviderInterface::class);
        $passwordProvider->method('getPasswords')->willReturn(['123', 'xxx123']);

        $archiveHandler = $this->createMock(ArchiveInterface::class);
        $archiveHandler->method('canSupport')
            ->willReturn(true);

        $archiveHandler->method('extract')
            ->willReturn(true);

        $archiveHandlerOther = $this->createMock(ArchiveInterface::class);
        $archiveHandlerOther->method('canSupport')
            ->willReturn(true);

        $archiveHandlerOther->method('extract')
            ->willThrowException(new \RuntimeException('Duplicate!'));

        // Устанавливаем 2 обработчика один вернет true. другой Exception
        $this->extractor->withPasswords($passwordProvider)
            ->withHandlers([
                $archiveHandler,
                $archiveHandlerOther,
            ])
            ->onFailure(fn ($e) => throw new \Exception('New: '.$e->getMessage()));

        // Ожидаем, что будет исключение не будет выброшено, так как после первого обработчика будет возвращено true

        $result = $this->extractor->extract('/path/to/archive.zip');

        $this->assertTrue($result);
    }

    public function testExtractionNotCallPasswords(): void
    {
        $passwordProvider = $this->createMock(PasswordProviderInterface::class);
        $passwordProvider->method('getPasswords')
            ->willThrowException(new \RuntimeException('Мы не должны запрашивать пароль когда это не нужно'));

        $archiveHandler = $this->createMock(ArchiveInterface::class);
        $archiveHandler->method('extract')
            ->willReturn(true);

        $archiveHandler->method('canSupport')
            ->willReturn(true);

        $this->extractor->withPasswords($passwordProvider)
            ->withHandler($archiveHandler)
            ->onSuccess(fn ($filePath) => $filePath.' extracted successfully.')
            ->onFailure(fn ($filePath) => 'Failed to extract '.$filePath);

        $result = $this->extractor->extract('/path/to/archive.zip');

        $this->assertEquals('/path/to/archive.zip extracted successfully.', $result);
    }
}
