<?php

namespace EsploraArchive\Handlers;

use Esplora\Decompresso\Contracts\ArchiveInterface;

/**
 * Обработчик для работы с GZIP-архивами.
 *
 * Этот класс реализует интерфейс ArchiveInterface и предоставляет функциональность для извлечения GZIP-архивов.
 */
class GzipArchiveHandler implements ArchiveInterface
{
    /**
     * Извлекает содержимое GZIP-архива в указанное место.
     *
     * Метод открывает GZIP-архив и извлекает его содержимое в указанный каталог. Если каталог не существует, он должен быть создан.
     *
     * @param string $filePath Путь к GZIP-архиву, который нужно извлечь.
     * @param string $destination Каталог, в который будет извлечен архив. Если каталог не существует, он должен быть создан.
     * @param iterable $passwords Не используется для GZIP-архивов, этот параметр игнорируется.
     * @return bool Возвращает true, если извлечение прошло успешно, и false в противном случае.
     */
    public function extract(string $filePath, string $destination, iterable $passwords = []): bool
    {
        // Открываем GZIP-файл для чтения
        $gz = gzopen($filePath, 'rb');

        if ($gz === false) {
            return false;
        }

        // Создаем выходной файл в указанной директории
        $outputFilePath = rtrim($destination, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . basename($filePath, '.gz');
        $output = fopen($outputFilePath, 'wb');

        if ($output === false) {
            gzclose($gz);
            return false;
        }

        // Читаем данные из GZIP-архива и записываем их в выходной файл
        while (!gzeof($gz)) {
            $buffer = gzread($gz, 4096);
            fwrite($output, $buffer);
        }

        fclose($output);
        gzclose($gz);

        return true;
    }
}