<?php

namespace Esplora\Decompresso\Adapters;

use Esplora\Decompresso\Concerns\SupportsMimeTypes;
use Esplora\Decompresso\Contracts\ArchiveAdapterInterface;
use Esplora\Decompresso\Contracts\PasswordProviderInterface;
use Exception;
use RarArchive;

/**
 * Handler for RAR archive files.
 *
 * This class implements the ArchiveInterface and provides functionality for extracting RAR archives,
 * including support for password-protected archives.
 */
class RarArchiveAdapter implements ArchiveAdapterInterface
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
            'application/vnd.rar',
            'application/x-rar-compressed',
        ];
    }

    /**
     * Extracts the contents of a RAR archive to the specified location.
     *
     * @param string                    $filePath    Path to the RAR archive to extract.
     * @param string                    $destination Directory where the archive will be extracted. The directory will be created if it does not exist.
     * @param PasswordProviderInterface $passwords   List of passwords to attempt for extracting a password-protected archive. Can be an array or other iterable object.
     *
     * @return bool Returns true if extraction was successful, false otherwise.
     */
    public function extract(string $filePath, string $destination, PasswordProviderInterface $passwords): bool
    {
        $rar = RarArchive::open($filePath);

        if (! $rar) {
            // Точно ли нужно? Сомниваюсь, что это правильно TODO: <--- Проверить
            return false;
        }

        $entries = $rar->getEntries();

        if ($entries !== false) {
            foreach ($entries as $entry) {
                $entry->extract($destination);
            }
            $rar->close();

            return true;
        }

        // Attempt to extract the archive with each password
        foreach ($passwords->getPasswords() as $password) {
            try {
                $rar->setPassword($password);

                foreach ($rar->getEntries() as $entry) {
                    $entry->extract($destination);
                }

                $rar->close();

                return true;
            } catch (Exception) {
                // Try the next password
                continue;
            }
        }

        return false;
    }
}
