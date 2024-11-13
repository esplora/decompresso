<?php

namespace Esplora\Lumos\Adapters;

use Esplora\Lumos\Concerns\SupportsMimeTypes;
use Esplora\Lumos\Contracts\AdapterInterface;
use Esplora\Lumos\Contracts\PasswordProviderInterface;
use PharData;

/**
 * Handler for TAR archive files.
 *
 * This class implements the ArchiveInterface and provides functionality for extracting TAR archives.
 */
class TarAdapter implements AdapterInterface
{
    use SupportsMimeTypes;

    /**
     * Returns the list of supported MIME types.
     *
     * @return array<string> Array of MIME types supported by this handler.
     */
    protected function supportedMimeTypes(): array
    {
        return [
            'application/x-tar',
        ];
    }

    /**
     * Extracts the contents of a TAR archive to the specified location.
     *
     * @param string                    $filePath    Path to the TAR archive to extract.
     * @param string                    $destination Directory where the archive will be extracted. The directory will be created if it does not exist.
     * @param PasswordProviderInterface $passwords   List of passwords, not used for TAR archives.
     *
     * @return bool Returns true if extraction was successful, false otherwise.
     */
    public function extract(string $filePath, string $destination, PasswordProviderInterface $passwords): bool
    {
        // Ensure the destination directory exists or create it
        if (! is_dir($destination) && ! mkdir($destination, 0777, true) && ! is_dir($destination)) {
            return false;
        }

        $tar = new PharData($filePath);

        $tar->extractTo($destination);

        return true;
    }
}
