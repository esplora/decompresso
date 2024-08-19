<?php

declare(strict_types=1);

namespace Esplora\Decompresso\Tests\Concerns;

use Esplora\Decompresso\Concerns\SupportsFileExtensions;
use Esplora\Decompresso\Tests\Cleanup;
use PHPUnit\Framework\TestCase;

class SupportsFileExtensionsTest extends TestCase
{
    use Cleanup;
    public function testIsFileExtensionAllowed(): void
    {
        $instance = new class
        {
            use SupportsFileExtensions;

            protected function allowedExtensions(): array
            {
                return ['txt', 'zip'];
            }
        };

        $this->assertTrue($instance->canSupport('file.zip'));
        $this->assertFalse($instance->canSupport('image.jpg'));
    }
}
