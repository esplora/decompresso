<?php

declare(strict_types=1);

namespace Esplora\Decompresso\Tests\Concerns;

use Esplora\Decompresso\Concerns\Always;
use Esplora\Decompresso\Concerns\SupportsMimeTypes;
use Esplora\Decompresso\Tests\Cleanup;
use PHPUnit\Framework\TestCase;

class SupportsMimeTypesTest extends TestCase
{
    use Cleanup;

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

        $this->assertTrue($instance->canSupport($this->getFixturesDir('/zip/simple.zip')));
        $this->assertTrue($instance->canSupport($this->getFixturesDir('/zip/protected.zip')));
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

        $this->assertFalse($instance->canSupport($this->getFixturesDir('/zip/protected.zip')));
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

        $this->assertFalse($instance->canSupport($this->getFixturesDir('/zip/protected.zip')));
    }
}
