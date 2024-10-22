<?php

namespace Esplora\Decompresso\Adapters;

use Esplora\Decompresso\Concerns\SupportsMimeTypes;
use Esplora\Decompresso\Contracts\PasswordProviderInterface;
use Symfony\Component\Process\Process;

class OfficeCryptoToolAdapter
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
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document', // DOCX
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',      // XLSX
            'application/vnd.openxmlformats-officedocument.presentationml.presentation', // PPTX
            'application/msword', // DOC
            'application/vnd.ms-excel', // XLS
            'application/vnd.ms-powerpoint', // PPT
        ];
    }

    /**
     * Decrypts an office file using msoffcrypto-tool.
     *
     * @param string                    $filePath    Path to the office file to decrypt.
     * @param string                    $destination Path where the decrypted file will be saved.
     * @param PasswordProviderInterface $passwords   List of passwords to try for decrypting the file.
     *
     * @return bool Returns true if decryption was successful, false otherwise.
     */
    public function decrypt(string $filePath, string $destination, PasswordProviderInterface $passwords): bool
    {
        // First, try to open the file without a password
        if ($this->tryDecrypting($filePath, $destination)) {
            return true; // Successfully opened without password
        }

        // If that fails, try each provided password
        foreach ($passwords->getPasswords() as $password) {
            if ($this->tryDecrypting($filePath, $destination, $password)) {
                return true; // Successfully decrypted with password
            }
        }

        return false; // Decryption failed
    }

    /**
     * Attempts to decrypt the office file with the provided password.
     * If no password is provided, it attempts to open the file without a password.
     *
     * @param string      $filePath    Path to the office file.
     * @param string      $destination Path where the decrypted file will be saved.
     * @param string|null $password    Optional password to attempt for unlocking the file.
     *
     * @return bool Returns true if decryption was successful, false otherwise.
     */
    protected function tryDecrypting(string $filePath, string $destination, ?string $password = null): bool
    {
        $command = [
            'msoffcrypto-tool',
            '--decrypt',
        ];

        // Add password option if provided
        if ($password !== null) {
            $command[] = '--password='.$password;
        }

        // Add file paths
        $command[] = $filePath;
        $command[] = $destination;

        $process = new Process($command);
        $process->run();

        return $process->isSuccessful();
    }
}
