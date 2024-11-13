<?php

declare(strict_types=1);

namespace Esplora\Lumos\Tests\Concerns;

use Esplora\Lumos\Concerns\SupportsFileSize;
use PHPUnit\Framework\TestCase;

class SupportsFileSizeTest extends TestCase
{
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

        $this->assertFalse($instance->canSupport(__DIR__.'/../fixtures//zip/protected.zip'));
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

        $this->assertTrue($instance->canSupport(__DIR__.'/../fixtures/zip/protected.zip'));
    }
}
