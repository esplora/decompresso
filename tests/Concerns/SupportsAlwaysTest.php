<?php

declare(strict_types=1);

namespace Esplora\Decompresso\Tests\Concerns;

use Esplora\Decompresso\Concerns\SupportsAlways;
use PHPUnit\Framework\TestCase;

class SupportsAlwaysTest extends TestCase
{
    public function testAlwaysReturnsTrue(): void
    {
        $class = new class
        {
            use SupportsAlways;
        };

        $this->assertTrue($class->canSupport('/path/to/file.zip'));
        $this->assertTrue($class->canSupport('/path/to/anotherfile.rar'));
        $this->assertTrue($class->canSupport('/any/random/path.txt'));
    }
}
