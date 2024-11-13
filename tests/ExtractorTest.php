<?php

declare(strict_types=1);

namespace Esplora\Lumos\Tests;

use Esplora\Lumos\Contracts\AdapterInterface;
use Esplora\Lumos\Contracts\PasswordProviderInterface;
use Esplora\Lumos\Extractor;
use PHPUnit\Framework\TestCase;

class ExtractorTest extends TestCase
{
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

        $archiveHandler = $this->createMock(AdapterInterface::class);
        $archiveHandler->method('extract')
            ->willReturn(true);

        $archiveHandler->method('canSupport')
            ->willReturn(true);

        $this->extractor->withPasswords($passwordProvider)
            ->withAdapter($archiveHandler)
            ->onSuccess(fn ($filePath) => $filePath.' extracted successfully.')
            ->onFailure(fn ($filePath) => 'Failed to extract '.$filePath);

        $result = $this->extractor->extract('/path/to/archive.zip');

        $this->assertEquals('/path/to/archive.zip extracted successfully.', $result);
    }

    public function testExtractionPasswordFailure(): void
    {
        $passwordProvider = $this->createMock(PasswordProviderInterface::class);
        $passwordProvider->method('getPasswords')->willReturn(['123', 'xxx123']);

        $archiveHandler = $this->createMock(AdapterInterface::class);
        $archiveHandler->method('extract')
            ->willReturn(false); // Simulate extraction failure due to incorrect password

        // Set up handler to throw exception on failure
        $this->extractor->withPasswords($passwordProvider)
            ->withAdapter($archiveHandler)
            ->onPasswordFailure(fn ($filePath) => throw new \Exception("Failed to extract archive: {$filePath}"));

        // Expect an exception to be thrown
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Failed to extract archive: /path/to/archive.zip');

        $this->extractor->extract('/path/to/archive.zip');
    }

    public function testExtractionExceptionFailure(): void
    {
        $passwordProvider = $this->createMock(PasswordProviderInterface::class);
        $passwordProvider->method('getPasswords')->willReturn(['123', 'xxx123']);

        $archiveHandler = $this->createMock(AdapterInterface::class);
        $archiveHandler->method('canSupport')
            ->willReturn(true);

        $archiveHandler->method('extract')
            ->willThrowException(new \RuntimeException('Failed to extract archive.'));

        // Set up handler to throw exception on general failure
        $this->extractor->withPasswords($passwordProvider)
            ->withAdapter($archiveHandler)
            ->onFailure(fn ($e) => throw new \Exception('Error: '.$e->getMessage()));

        // Expect an exception to be thrown
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Error: Failed to extract archive.');

        $this->extractor->extract('/path/to/archive.zip');
    }

    public function testExtractionWithOutContinueOnSuccess(): void
    {
        $passwordProvider = $this->createMock(PasswordProviderInterface::class);
        $passwordProvider->method('getPasswords')->willReturn(['123', 'xxx123']);

        $archiveHandler = $this->createMock(AdapterInterface::class);
        $archiveHandler->method('canSupport')
            ->willReturn(true);

        $archiveHandler->method('extract')
            ->willReturn(true);

        $archiveHandlerOther = $this->createMock(AdapterInterface::class);
        $archiveHandlerOther->method('canSupport')
            ->willReturn(true);

        $archiveHandlerOther->method('extract')
            ->willThrowException(new \RuntimeException('Duplicate!'));

        // Set up two handlers: one returns true, the other throws an exception
        $this->extractor->withPasswords($passwordProvider)
            ->withAdapters([
                $archiveHandler,
                $archiveHandlerOther,
            ])
            ->onFailure(fn ($e) => throw new \Exception('Error: '.$e->getMessage()));

        // Expect no exception, as the first handler returns true
        $result = $this->extractor->extract('/path/to/archive.zip');

        $this->assertTrue($result);
    }

    public function testExtractionNotCallPasswords(): void
    {
        $passwordProvider = $this->createMock(PasswordProviderInterface::class);
        $passwordProvider->method('getPasswords')
            ->willThrowException(new \RuntimeException('Passwords should not be requested when not needed'));

        $archiveHandler = $this->createMock(AdapterInterface::class);
        $archiveHandler->method('extract')
            ->willReturn(true);

        $archiveHandler->method('canSupport')
            ->willReturn(true);

        $this->extractor->withPasswords($passwordProvider)
            ->withAdapter($archiveHandler)
            ->onSuccess(fn ($filePath) => $filePath.' extracted successfully.')
            ->onFailure(fn ($filePath) => 'Failed to extract '.$filePath);

        $result = $this->extractor->extract('/path/to/archive.zip');

        $this->assertEquals('/path/to/archive.zip extracted successfully.', $result);
    }
}
