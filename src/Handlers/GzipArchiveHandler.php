<?php

namespace Esplora\Decompresso\Handlers;

use Esplora\Decompresso\Contracts\ArchiveInterface;
use Exception;

/**
 * Обработчик для работы с архивами формата GZIP.
 *
 * Этот класс реализует интерфейс ArchiveInterface и предоставляет функциональность для извлечения GZIP-архивов.
 * GZIP-файлы обычно содержат один файл, поэтому извлечение просто распаковывает его.
 */
class GzipArchiveHandler implements ArchiveInterface
{
    /**
     * Извлекает содержимое GZIP-архива в указанное место.
     *
     * @param string   $filePath    Путь к GZIP-архиву, который нужно извлечь.
     * @param string   $destination Каталог, в который будет извлечен архив. Если каталог не существует, он должен быть создан.
     * @param iterable $passwords   Список паролей, не используется для GZIP-архивов.
     *
     * @return bool Возвращает true, если извлечение прошло успешно, и false в противном случае.
     */
    public function extract(string $filePath, string $destination, iterable $passwords = []): bool
    {
        $outputFile = $destination.basename($filePath, '.gz');

        try {
            $filePointer = gzopen($filePath, 'rb');

            if (! $filePointer) {
                return false;
            }

            $outputPointer = fopen($outputFile, 'wb');

            if (! $outputPointer) {
                gzclose($filePointer);

                return false;
            }

            while (! gzeof($filePointer)) {
                fwrite($outputPointer, gzread($filePointer, 4096));
            }

            gzclose($filePointer);
            fclose($outputPointer);

            return true;
        } catch (Exception $e) {
            return false;
        }
    }
}
