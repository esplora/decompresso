<?php

namespace Esplora\Lumos\Concerns;

use Esplora\Lumos\Contracts\ExtractionSummaryInterface;
use Esplora\Lumos\Summary;

trait HasExtractionSummary
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
