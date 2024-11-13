<?php

declare(strict_types=1);

namespace Esplora\Lumos\Tests\Concerns;

use Esplora\Lumos\Concerns\SupportsMimeTypes;
use Esplora\Lumos\Tests\Cleanup;
use PHPUnit\Framework\TestCase;

class SupportsMimeTypesTest extends TestCase
{
    public function testCanSupportWithSupportedMimeType(): void
    {
        $instance = new class
        {
            use SupportsMimeTypes;

            protected function supportedMimeTypes(): array
            {
                return ['application/zip', 'application/x-tar'];
            }
        };

        $this->assertTrue($instance->canSupport(__DIR__ . '/../fixtures/zip/simple.zip'));
        $this->assertTrue($instance->canSupport(__DIR__ . '/../fixtures/zip/protected.zip'));
    }

    public function testCanSupportWithUnsupportedMimeType(): void
    {
        $instance = new class
        {
            use SupportsMimeTypes;

            protected function supportedMimeTypes(): array
            {
                return ['application/x-blorb'];
            }
        };

        $this->assertFalse($instance->canSupport(__DIR__ . '/../fixtures/zip/protected.zip'));
    }

    public function testCanSupportWithEmptySupportedMimeTypes(): void
    {
        $instance = new class
        {
            use SupportsMimeTypes;

            protected function supportedMimeTypes(): array
            {
                return [];
            }
        };

        $this->assertFalse($instance->canSupport(__DIR__ . '/../fixtures/zip/protected.zip'));
    }
}
