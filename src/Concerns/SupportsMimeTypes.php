<?php

declare(strict_types=1);

namespace Esplora\Lumos\Concerns;

use Symfony\Component\Mime\MimeTypes;

trait SupportsMimeTypes
{
    /**
     * Checks if the handler supports the archive format using Symfony Mime.
     *
     * @param string $filePath Path to the archive to check.
     *
     * @return bool Returns true if the archive format is supported, false otherwise.
     */
    public function canSupport(string $filePath): bool
    {
        $fileMimeType = (new MimeTypes)->guessMimeType($filePath);

        return in_array($fileMimeType, $this->supportedMimeTypes(), true);
    }

    /**
     * Returns the list of supported MIME types.
     *
     * @return array<string> Array of MIME types supported by this handler.
     */
    abstract protected function supportedMimeTypes(): array;
}
