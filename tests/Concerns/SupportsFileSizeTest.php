<?php

declare(strict_types=1);

namespace Esplora\Decompresso\Tests\Concerns;

use Esplora\Decompresso\Concerns\SupportsFileSize;
use Esplora\Decompresso\Tests\Cleanup;
use PHPUnit\Framework\TestCase;

class SupportsFileSizeTest extends TestCase
{
    use Cleanup;

    public function testFileSizeBelowLimit(): void
    {
        $instance = new class
        {
            use SupportsFileSize;

            protected function getMaxFileSize(): int
            {
                return 1; // 1 byte
            }
        };

        $this->assertFalse($instance->canSupport($this->getFixturesDir('/zip/protected.zip')));
    }

    public function testFileSizeWithinLimit(): void
    {
        $instance = new class
        {
            use SupportsFileSize;

            protected function getMaxFileSize(): int
            {
                return 1048576; // 1 MB
            }
        };

        $this->assertTrue($instance->canSupport($this->getFixturesDir('/zip/protected.zip')));
    }
}
