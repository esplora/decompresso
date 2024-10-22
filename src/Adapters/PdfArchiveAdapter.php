<?php

namespace Esplora\Decompresso\Adapters;

use Esplora\Decompresso\Concerns\SupportsMimeTypes;
use Esplora\Decompresso\Contracts\ArchiveAdapterInterface;
use Esplora\Decompresso\Contracts\PasswordProviderInterface;
use Symfony\Component\Process\Process;

/**
 * Handler for PDF files with password protection.
 *
 * This class implements the ArchiveAdapterInterface and provides functionality for handling PDF files,
 * specifically for removing passwords using qpdf.
 */
class PdfArchiveAdapter implements ArchiveAdapterInterface
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
            'application/pdf',
        ];
    }

    /**
     * Extracts or removes the password from a PDF file.
     *
     * This method uses the qpdf utility to decrypt a password-protected PDF file.
     * It tries each password provided until it finds the correct one.
     *
     * @param string                    $filePath    Path to the PDF file to process.
     * @param string                    $destination Path where the unlocked PDF will be saved.
     * @param PasswordProviderInterface $passwords   List of passwords to try for decrypting the PDF file.
     *
     * @return bool Returns true if the decryption was successful, false otherwise.
     */
    public function extract(string $filePath, string $destination, PasswordProviderInterface $passwords): bool
    {
        foreach ($passwords->getPasswords() as $password) {
            if ($this->tryDecrypting($filePath, $destination, $password)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Attempts to decrypt the PDF file with the provided password.
     *
     * @param string $filePath    Path to the PDF file.
     * @param string $destination Path where the unlocked PDF will be saved.
     * @param string $password    Password to attempt for unlocking the PDF.
     *
     * @return bool Returns true if decryption was successful, false otherwise.
     */
    protected function tryDecrypting(string $filePath, string $destination, string $password): bool
    {
        // Ensure the destination directory exists or create it
        if (! is_dir($destination) && ! mkdir($destination, 0777, true) && ! is_dir($destination)) {
            return false;
        }

        $command = [
            'qpdf',
            '--password='.$password,
            '--decrypt',
            $filePath,
            $destination.basename($filePath),
        ];

        $process = new Process($command);
        $process->run();

        // Check if the process was successful
        if ($process->isSuccessful()) {
            return true;
        }

        return false;
    }
}
