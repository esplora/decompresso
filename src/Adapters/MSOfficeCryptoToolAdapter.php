<?php

namespace Esplora\Lumos\Adapters;

use Esplora\Lumos\Concerns\Decryptable;
use Esplora\Lumos\Concerns\SupportsMimeTypes;
use Esplora\Lumos\Contracts\AdapterInterface;
use Symfony\Component\Process\Process;

class MSOfficeCryptoToolAdapter implements AdapterInterface
{
    use Decryptable, SupportsMimeTypes;

    /**
     * @param string $bin
     */
    public function __construct(protected string $bin = 'msoffcrypto-tool') {}

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
        // Need save the file with the same name
        $destination .= basename($filePath);

        $command = [
            $this->bin,
            $filePath,
            $destination,
            $password !== null ? '--password='.$password : '--test',
        ];

        $process = new Process($command);
        $process->run();


        // When password exist and the process is successful, the file is decrypted
        if ($password !== null) {
            $this->summary()
                ->addStepWithProcess($process->isSuccessful(), $process, $password);

            return $process->isSuccessful();
        }

        // When password is not provided, check output for 'not encrypted' message
        if (! str_contains($process->getErrorOutput(), 'not encrypted')) {
            $this->summary()
                ->addStepWithProcess(false, $process, $password);

            return false;
        }

        copy($filePath, $destination);

        $this->summary()
            ->addStepWithProcess(true, $process, $password);

        return true;
    }

    /**
     * Checks if the required tools or libraries are installed for this adapter.
     *
     * @return bool Returns true if the environment is properly configured, false otherwise.
     */
    public function isSupportedEnvironment(): bool
    {
        $command = [$this->bin, '--help'];

        $process = new Process($command);
        $process->run();

        return $process->isSuccessful();
    }
}
