<?php

namespace Esplora\Decompresso\Adapters;

use Esplora\Decompresso\Concerns\SupportsMimeTypes;
use Esplora\Decompresso\Contracts\ArchiveAdapterInterface;
use Esplora\Decompresso\Contracts\PasswordProviderInterface;

/**
 * Handler for GZIP archive files.
 *
 * This class implements the ArchiveInterface and provides functionality for extracting GZIP archives.
 * GZIP files typically contain a single file, so extraction involves simply unpacking it.
 */
class GzipArchiveAdapterAdapter implements ArchiveAdapterInterface
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
            'application/gzip',
            'application/x-gzip',
        ];
    }

    /**
     * Extracts the contents of a GZIP archive to the specified location.
     *
     * @param string                    $filePath    Path to the GZIP archive to extract.
     * @param string                    $destination Directory where the archive will be extracted. The directory will be created if it does not exist.
     * @param PasswordProviderInterface $passwords   List of passwords, not used for GZIP archives.
     *
     * @return bool Returns true if extraction was successful, false otherwise.
     */
    public function extract(string $filePath, string $destination, PasswordProviderInterface $passwords): bool
    {
        $outputFile = $destination.basename($filePath, '.gz');

        // Ensure the destination directory exists or create it
        if (! is_dir($destination) && ! mkdir($destination, 0777, true) && ! is_dir($destination)) {
            return false;
        }

        $filePointer = gzopen($filePath, 'rb');

        if (! $filePointer) {
            return false;
        }

        $outputPointer = fopen($outputFile, 'wb');

        if (! $outputPointer) {
            gzclose($filePointer);

            return false;
        }

        while (! gzeof($filePointer)) {
            fwrite($outputPointer, gzread($filePointer, 4096));
        }

        gzclose($filePointer);
        fclose($outputPointer);

        return true;
    }
}
