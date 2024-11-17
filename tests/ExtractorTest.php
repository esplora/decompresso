<?php

declare(strict_types=1);

namespace Esplora\Lumos\Tests;

use Esplora\Lumos\Contracts\AdapterInterface;
use Esplora\Lumos\Contracts\PasswordProviderInterface;
use Esplora\Lumos\Contracts\SummaryInterface;
use Esplora\Lumos\Extractor;
use Illuminate\Support\Collection;
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

    public function testBaseExtractionSuccess(): void
    {
        $passwordProvider = $this->createMock(PasswordProviderInterface::class);
        $passwordProvider->method('getPasswords')->willReturn(['123', 'xxx123']);

        $archiveHandler = $this->createMock(AdapterInterface::class);
        $archiveHandler->method('extract')
            ->willReturn($this->createSummary());

        $archiveHandler->method('canSupport')
            ->willReturn(true);

        $result = $this->extractor->withPasswords($passwordProvider)
            ->withAdapter($archiveHandler)
            ->extract('/path/to/archive.zip');

        $this->assertInstanceOf(SummaryInterface::class, $result);
        $this->assertTrue($result->isSuccessful());
    }

    public function testBaseExtractionEmptyAdaptersFailure(): void
    {
        $passwordProvider = $this->createMock(PasswordProviderInterface::class);
        $passwordProvider->method('getPasswords')->willReturn(['123', 'xxx123']);

        $archiveHandler = $this->createMock(AdapterInterface::class);
        $archiveHandler->method('canSupport')
            ->willReturn(false);

        // Expect an exception to be thrown
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('No adapter found for file: /path/to/archive.zip');

        // Set up handler to throw exception on failure
        $this->extractor
            ->withPasswords($passwordProvider)
            ->withAdapter($archiveHandler)
            ->extract('/path/to/archive.zip');
    }

    public function testBaseExtractionExceptionFailure(): void
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

    public function testBaseExtractionWithOutContinueOnSuccess(): void
    {
        $passwordProvider = $this->createMock(PasswordProviderInterface::class);
        $passwordProvider->method('getPasswords')->willReturn(['123', 'xxx123']);

        $archiveHandler = $this->createMock(AdapterInterface::class);
        $archiveHandler->method('canSupport')
            ->willReturn(true);

        $archiveHandler->method('extract')
            ->willReturn($this->createSummary());

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

        $this->assertInstanceOf(SummaryInterface::class, $result);
        $this->assertTrue($result->isSuccessful());
    }

    public function testBaseExtractionNotCallPasswords(): void
    {
        $passwordProvider = $this->createMock(PasswordProviderInterface::class);
        $passwordProvider->method('getPasswords')
            ->willThrowException(new \RuntimeException('Passwords should not be requested when not needed'));

        $archiveHandler = $this->createMock(AdapterInterface::class);
        $archiveHandler->method('extract')
            ->willReturn($this->createSummary());

        $archiveHandler->method('canSupport')
            ->willReturn(true);

        $result = $this->extractor->withPasswords($passwordProvider)
            ->withAdapter($archiveHandler)
            ->extract('/path/to/archive.zip');

        $this->assertInstanceOf(SummaryInterface::class, $result);
        $this->assertTrue($result->isSuccessful());
    }


    private function createSummary(bool $status = true, Collection|array $steps = []): SummaryInterface
    {
        $summary = $this->createMock(SummaryInterface::class);

        $summary->method('isSuccessful')
            ->willReturn($status);

        $summary->method('steps')
            ->willReturn(Collection::wrap($steps));

        return $summary;
    }
}
