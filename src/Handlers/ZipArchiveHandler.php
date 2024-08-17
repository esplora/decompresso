<?php

namespace Esplora\Decompresso\Handlers;

use Esplora\Decompresso\Concerns\SupportsMimeTypes;
use Esplora\Decompresso\Contracts\ArchiveInterface;
use Esplora\Decompresso\Contracts\PasswordProviderInterface;
use ZipArchive;

/**
 * Обработчик для работы с архивами формата ZIP.
 *
 * Этот класс реализует интерфейс ArchiveInterface и предоставляет функциональность для извлечения ZIP-архивов,
 * включая поддержку паролей для защищенных архивов.
 */
class ZipArchiveHandler implements ArchiveInterface
{
    use SupportsMimeTypes;

    /**
     * Возвращает список поддерживаемых MIME-типов.
     *
     * @return array<string> Массив MIME-типов, которые поддерживает обработчик.
     */
    protected function supportedMimeTypes(): array
    {
        return [
            'application/zip',
        ];
    }

    /**
     * Извлекает содержимое ZIP-архива в указанное место.
     *
     * Метод открывает ZIP-архив, пытается извлечь его содержимое в указанный каталог. Если архив защищен паролем,
     * метод будет пробовать каждый из предоставленных паролей, пока не найдет подходящий.
     *
     * @param string   $filePath    Путь к ZIP-архиву, который нужно извлечь.
     * @param string   $destination Каталог, в который будет извлечен архив. Если каталог не существует, он должен быть создан.
     * @param PasswordProviderInterface $passwords   Список паролей для попытки извлечения защищенного паролем архива. Может быть массивом или другим iterable объектом.
     *
     * @return bool Возвращает true, если извлечение прошло успешно, и false в противном случае.
     */
    public function extract(string $filePath, string $destination, PasswordProviderInterface $passwords): bool
    {
        $zip = new ZipArchive;
        $res = $zip->open($filePath);

        if ($res === true) {
            if ($this->tryExtracting($zip, $destination, $passwords)) {
                $zip->close();

                return true;
            }
            $zip->close();
        }

        return false;
    }

    /**
     * Попытка извлечь содержимое архива с использованием предоставленных паролей.
     *
     * Метод пробует извлечь архив сначала без пароля, а затем с каждым из предоставленных паролей. Если ни один из
     * паролей не подходит, извлечение завершится неудачей.
     *
     * @param \ZipArchive $zip         Экземпляр ZipArchive, который нужно извлечь.
     * @param string      $destination Каталог, в который нужно извлечь содержимое архива.
     * @param PasswordProviderInterface    $passwords   Список паролей для попытки извлечения защищенного паролем архива.
     *
     * @return bool Возвращает true, если извлечение прошло успешно, и false в противном случае.
     */
    protected function tryExtracting(ZipArchive $zip, string $destination, PasswordProviderInterface $passwords): bool
    {
        // Пробуем извлечь архив без пароля
        if ($zip->extractTo($destination)) {
            return true;
        }

        // Пробуем извлечь архив с каждым паролем
        foreach ($passwords->getPasswords() as $password) {
            if ($zip->setPassword($password) && $zip->extractTo($destination)) {
                return true;
            }
        }

        return false;
    }
}
