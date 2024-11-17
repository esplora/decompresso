<?php

namespace Esplora\Lumos\Concerns;

use Esplora\Lumos\Summary;

trait HasSummary
{
    private ?Summary $summary = null;

    /**
     * Initializes and returns the current summary object.
     *
     * @return Summary
     */
    protected function summary(): Summary
    {
        $this->summary ??= new Summary;

        return $this->summary;
    }
}
