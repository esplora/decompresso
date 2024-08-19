<?php

declare(strict_types=1);

namespace Esplora\Decompresso\Tests;

use Esplora\Decompresso\Concerns\Always;
use Esplora\Decompresso\Concerns\SupportsMimeTypes;
use PHPUnit\Framework\TestCase;

class SupportsTest extends TestCase
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

    public function testAlwaysReturnsTrue(): void
    {
        $class = new class
        {
            use Always;
        };

        $this->assertTrue($class->canSupport('/path/to/file.zip'));
        $this->assertTrue($class->canSupport('/path/to/anotherfile.rar'));
        $this->assertTrue($class->canSupport('/any/random/path.txt'));
    }
}
