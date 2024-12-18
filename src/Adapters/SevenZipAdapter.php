<?php

namespace Esplora\Lumos\Adapters;

use Esplora\Lumos\Concerns\Decryptable;
use Esplora\Lumos\Concerns\SupportsMimeTypes;
use Esplora\Lumos\Contracts\AdapterInterface;
use Symfony\Component\Process\Process;

/**
 * Handler for SevenZipArchive archive files.
 *
 * This class implements the ArchiveInterface and provides functionality for extracting .7z archives,
 * including support for passwords for protected archives.
 */
class SevenZipAdapter implements AdapterInterface
{
    use Decryptable, SupportsMimeTypes;

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
     * Attempts to extract the archive contents with an optional password.
     *
     * @param string      $filePath    Path to the 7-Zip archive.
     * @param string      $destination Directory for extracting the archive.
     * @param string|null $password    Password (optional).
     *
     * @return bool Returns true if extraction was successful, false otherwise.
     */
    protected function tryDecrypting(string $filePath, string $destination, ?string $password = null): bool
    {
        $command = [
            $this->bin,
            'x',
            $filePath,
            '-o'.$destination,
            '-scsUTF-8',
            '-y',
            $password !== null ? '-p'.$password : '-p',
            '-x!*',
        ];

        $process = new Process($command);
        $process->run();

        $this->summary()
            ->addStepWithProcess($process->isSuccessful(), $process, $password);

        return $process->isSuccessful();
    }

    /**
     * Checks if the required tools or libraries are installed for this adapter.
     *
     * @return bool Returns true if the environment is properly configured, false otherwise.
     */
    public function isSupportedEnvironment(): bool
    {
        $command = [$this->bin, 'i']; // i : Show information about supported formats

        $process = new Process($command);
        $process->run();

        return $process->isSuccessful();
    }
}
