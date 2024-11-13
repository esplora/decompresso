<?php

namespace Esplora\Lumos\Adapters;

use Esplora\Lumos\Concerns\SupportsMimeTypes;
use Esplora\Lumos\Contracts\AdapterInterface;
use Esplora\Lumos\Contracts\PasswordProviderInterface;
use Symfony\Component\Process\Process;

/**
 * Handler for office files with password protection.
 *
 * This class implements the ArchiveAdapterInterface and provides functionality for handling office files,
 * specifically for removing passwords using LibreOffice.
 */
class LibreOfficeAdapter implements AdapterInterface
{
    use SupportsMimeTypes;

    /**
     * @param string $bin
     */
    public function __construct(protected string $bin = 'soffice') {}

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
     * Decrypts an office document by removing its password.
     *
     * This method uses the LibreOffice utility to decrypt a password-protected office file.
     * It first tries to open the file without a password, then tries each password provided until it finds the correct one.
     *
     * @param string                    $filePath    Path to the office file to process.
     * @param string                    $destination Path where the unlocked file will be saved.
     * @param PasswordProviderInterface $passwords   List of passwords to try for decrypting the office file.
     *
     * @return bool Returns true if the decryption was successful, false otherwise.
     */
    public function extract(string $filePath, string $destination, PasswordProviderInterface $passwords): bool
    {
        // First, attempt to open the file without a password
        if ($this->tryDecrypting($filePath, $destination)) {
            return true;
        }

        // If opening without password fails, try each provided password
        foreach ($passwords->getPasswords() as $password) {
            if ($this->tryDecrypting($filePath, $destination, $password)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Runs the LibreOffice command to convert the document.
     *
     * @param string      $filePath    Path to the office file.
     * @param string      $destination Path where the unlocked file will be saved.
     * @param string|null $password    Optional password to attempt for unlocking the office file.
     *
     * @return bool Returns true if the command was successful, false otherwise.
     */
    protected function tryDecrypting(string $filePath, string $destination, ?string $password = null): bool
    {
        // Ensure the destination directory exists or create it
        if (! is_dir($destination) && ! mkdir($destination, 0777, true) && ! is_dir($destination)) {
            return false;
        }

        $command = [
            $this->bin,
            '--headless',
            '--convert-to', pathinfo($filePath, PATHINFO_EXTENSION), // Convert to the same format
            '--outdir', dirname($destination),
            $filePath,
        ];

        // Add password option if provided
        if ($password !== null) {
            $command[] = '--password='.$password;
        }

        $process = new Process($command);
        $process->run();

        // Check if the process was successful
        return $process->isSuccessful();
    }

    /**
     * Checks if the required tools or libraries are installed for this adapter.
     *
     * @return bool Returns true if the environment is properly configured, false otherwise.
     */
    public function isSupportedEnvironment(): bool
    {
        $command = [$this->bin, '-v'];

        $process = new Process($command);
        $process->run();

        return $process->isSuccessful();
    }
}
