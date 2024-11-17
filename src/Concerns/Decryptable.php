<?php

namespace Esplora\Lumos\Concerns;

use Esplora\Lumos\Contracts\PasswordProviderInterface;

trait Decryptable
{
    use DirectoryEnsurer;

    /**
     * Removes the password or extract from an files using adapters.
     *
     * @param string                    $filePath    Path to the to file.
     * @param string                    $destination Path where the file will be saved.
     * @param PasswordProviderInterface $passwords   List of passwords to try for decrypting the file.
     *
     * @return bool Returns true if decryption was successful, false otherwise.
     */
    public function extract(string $filePath, string $destination, PasswordProviderInterface $passwords): bool
    {
        $this->ensureDirectoryExists($destination);

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
     * Attempts to decrypt the file with the provided password.
     *
     * @param string      $filePath    Path to the PDF file.
     * @param string      $destination Path where the unlocked PDF will be saved.
     * @param string|null $password    Password to attempt for unlocking the PDF.
     *
     * @return bool Returns true if decryption was successful, false otherwise.
     */
    abstract protected function tryDecrypting(string $filePath, string $destination, ?string $password = null): bool;
}
