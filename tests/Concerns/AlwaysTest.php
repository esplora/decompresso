<?php

declare(strict_types=1);

namespace Esplora\Decompresso\Tests\Concerns;

use Esplora\Decompresso\Concerns\Always;
use PHPUnit\Framework\TestCase;

class AlwaysTest extends TestCase
{
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
