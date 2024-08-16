<?php

namespace Esplora\Decompresso\Handlers;

use Esplora\Decompresso\Contracts\ArchiveInterface;

/**
 * Обработчик для архивов формата 7-Zip.
 *
 * Этот класс реализует интерфейс ArchiveInterface и предоставляет функциональность для извлечения архивов .7z,
 * включая поддержку паролей для защищённых архивов.
 */
class SevenZipArchiveHandler implements ArchiveInterface
{
    /**
     * Извлекает содержимое архива 7-Zip в указанную директорию.
     *
     * @param string   $filePath    Путь к архиву 7-Zip.
     * @param string   $destination Директория для извлечения архива. Директория будет создана, если её не существует.
     * @param iterable $passwords   Список паролей для защищённых архивов.
     *
     * @return bool Возвращает true, если извлечение прошло успешно, и false в противном случае.
     */
    public function extract(string $filePath, string $destination, iterable $passwords = []): bool
    {
        if ($this->tryExtract($filePath, $destination)) {
            return true; // Успешно извлечено без пароля
        }

        // Попытка извлечь архив с каждым паролем из списка
        foreach ($passwords as $password) {
            if ($this->tryExtract($filePath, $destination, $password)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Попытка извлечь содержимое архива с опциональным паролем.
     *
     * @param string      $filePath    Путь к архиву 7-Zip.
     * @param string      $destination Директория для извлечения архива.
     * @param string|null $password    Пароль (опционально).
     *
     * @return bool Возвращает true, если извлечение прошло успешно, и false в противном случае.
     */
    protected function tryExtract(string $filePath, string $destination, ?string $password = null): bool
    {
        // Формируем команду для извлечения
        $command = sprintf(
            '7z x %s -o%s %s -y',
            escapeshellarg($filePath),
            escapeshellarg($destination),
            $password ? '-p'.escapeshellarg($password) : ''
        );

        // Выполняем команду
        exec($command, $output, $returnVar);

        // Проверяем, успешно ли выполнена команда
        return $returnVar === 0;
    }
}
