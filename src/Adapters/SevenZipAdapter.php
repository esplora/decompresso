<?php

namespace Esplora\Lumos\Adapters;

use Esplora\Lumos\Concerns\SupportsMimeTypes;
use Esplora\Lumos\Contracts\AdapterInterface;
use Esplora\Lumos\Contracts\PasswordProviderInterface;
use Symfony\Component\Process\Process;

/**
 * Handler for SevenZipArchive archive files.
 *
 * This class implements the ArchiveInterface and provides functionality for extracting .7z archives,
 * including support for passwords for protected archives.
 */
class SevenZipAdapter implements AdapterInterface
{
    use SupportsMimeTypes;

    /**
     * @param string $bin
     */
    public function __construct(protected string $bin = '7z') {}

    /**
     * Returns the list of supported MIME types.
     *
     * @return array<string> Array of MIME types supported by this handler.
     */
    protected function supportedMimeTypes(): array
    {
        return [
            'application/x-7z-compressed',

            'application/gzip',
            'application/x-gzip',
            'application/vnd.rar',
            'application/x-rar-compressed',
            'application/x-tar',
            'application/zip',
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
        // Ensure the destination directory exists or create it
        if (! is_dir($destination) && ! mkdir($destination, 0777, true) && ! is_dir($destination)) {
            return false;
        }

        $command = [$this->bin, 'x', $filePath, '-o'.$destination, '-y', '-scsUTF-8'];

        if ($password) {
            $command[] = '-p'.$password;
        }

        $process = new Process($command);
        $process->run();

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
