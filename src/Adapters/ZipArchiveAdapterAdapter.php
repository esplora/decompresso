<?php

namespace Esplora\Decompresso\Adapters;

use Esplora\Decompresso\Concerns\SupportsMimeTypes;
use Esplora\Decompresso\Contracts\ArchiveAdapterInterface;
use Esplora\Decompresso\Contracts\PasswordProviderInterface;
use ZipArchive;

/**
 * Handler for ZIP archive files.
 *
 * This class implements the ArchiveInterface and provides functionality for extracting ZIP archives,
 * including support for password-protected archives.
 */
class ZipArchiveAdapterAdapter implements ArchiveAdapterInterface
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
            'application/zip',
        ];
    }

    /**
     * Extracts the contents of a ZIP archive to the specified location.
     *
     * Opens the ZIP archive and attempts to extract its contents to the given directory. If the archive is password-protected,
     * the method will try each provided password until it finds the correct one.
     *
     * @param string                    $filePath    Path to the ZIP archive to extract.
     * @param string                    $destination Directory where the archive will be extracted. The directory will be created if it does not exist.
     * @param PasswordProviderInterface $passwords   List of passwords to try for extracting a password-protected archive. Can be an array or any iterable object.
     *
     * @return bool Returns true if extraction was successful, false otherwise.
     */
    public function extract(string $filePath, string $destination, PasswordProviderInterface $passwords): bool
    {
        $zip = new ZipArchive;
        $res = $zip->open($filePath);

        if ($res === true) {
            if ($this->tryExtracting($zip, $destination, $passwords)) {
                $zip->close();

                return true;
            }
            $zip->close();
        }

        return false;
    }

    /**
     * Attempts to extract the archive using the provided passwords.
     *
     * Tries to extract the archive first without a password, and then with each of the provided passwords.
     * If none of the passwords work, extraction will fail.
     *
     * @param \ZipArchive               $zip         ZipArchive instance to extract.
     * @param string                    $destination Directory where the archive contents should be extracted.
     * @param PasswordProviderInterface $passwords   List of passwords to try for extracting the password-protected archive.
     *
     * @return bool Returns true if extraction was successful, false otherwise.
     */
    protected function tryExtracting(ZipArchive $zip, string $destination, PasswordProviderInterface $passwords): bool
    {
        // Try extracting the archive without a password
        if ($zip->extractTo($destination)) {
            return true;
        }

        // Try extracting the archive with each password
        foreach ($passwords->getPasswords() as $password) {
            if ($zip->setPassword($password) && $zip->extractTo($destination)) {
                return true;
            }
        }

        return false;
    }
}
