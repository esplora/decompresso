<?php

namespace Esplora\Decompresso\Adapters;

use Esplora\Decompresso\Concerns\SupportsMimeTypes;
use Esplora\Decompresso\Contracts\ArchiveAdapterInterface;
use Esplora\Decompresso\Contracts\PasswordProviderInterface;

/**
 * Handler for 7-Zip archive files.
 *
 * This class implements the ArchiveInterface and provides functionality for extracting .7z archives,
 * including support for passwords for protected archives.
 */
class SevenZipArchiveAdapter implements ArchiveAdapterInterface
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
            'application/x-7z-compressed',
        ];
    }

    /**
     * Extracts the contents of a 7-Zip archive to the specified directory.
     *
     * @param string                    $filePath    Path to the 7-Zip archive.
     * @param string                    $destination Directory where the archive will be extracted. The directory will be created if it does not exist.
     * @param PasswordProviderInterface $passwords   List of passwords for protected archives.
     *
     * @return bool Returns true if extraction was successful, false otherwise.
     */
    public function extract(string $filePath, string $destination, PasswordProviderInterface $passwords): bool
    {
        if ($this->tryExtract($filePath, $destination)) {
            return true; // Successfully extracted without a password
        }

        // Attempt to extract the archive with each password from the list
        foreach ($passwords->getPasswords() as $password) {
            if ($this->tryExtract($filePath, $destination, $password)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Attempts to extract the archive contents with an optional password.
     *
     * @param string      $filePath    Path to the 7-Zip archive.
     * @param string      $destination Directory for extracting the archive.
     * @param string|null $password    Password (optional).
     *
     * @return bool Returns true if extraction was successful, false otherwise.
     */
    protected function tryExtract(string $filePath, string $destination, ?string $password = null): bool
    {
        // Form the extraction command
        $command = sprintf(
            '7z x %s -o%s %s -y',
            escapeshellarg($filePath),
            escapeshellarg($destination),
            $password ? '-p'.escapeshellarg($password) : ''
        );

        // Execute the command
        exec($command, $output, $returnVar);

        // Check if the command was successful
        return $returnVar === 0;
    }
}
