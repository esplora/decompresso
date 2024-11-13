<?php

declare(strict_types=1);

namespace Esplora\Lumos\Tests\Concerns;

use Esplora\Lumos\Concerns\SupportsFileExtensions;
use PHPUnit\Framework\TestCase;

class SupportsFileExtensionsTest extends TestCase
{
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
