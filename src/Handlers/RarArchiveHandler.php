<?php

namespace Esplora\Decompresso\Handlers;

use Esplora\Decompresso\Contracts\ArchiveInterface;
use RarArchive;
use RarException;
use Exception;

/**
 * Обработчик для работы с архивами формата RAR.
 *
 * Этот класс реализует интерфейс ArchiveInterface и предоставляет функциональность для извлечения RAR-архивов,
 * включая поддержку паролей для защищенных архивов.
 */
class RarArchiveHandler implements ArchiveInterface
{
    /**
     * Извлекает содержимое RAR-архива в указанное место.
     *
     * @param string $filePath Путь к RAR-архиву, который нужно извлечь.
     * @param string $destination Каталог, в который будет извлечен архив. Если каталог не существует, он должен быть создан.
     * @param iterable $passwords Список паролей для попытки извлечения защищенного паролем архива. Может быть массивом или другим iterable объектом.
     * @return bool Возвращает true, если извлечение прошло успешно, и false в противном случае.
     */
    public function extract(string $filePath, string $destination, iterable $passwords = []): bool
    {
        try {
            $rar = RarArchive::open($filePath);
            if (!$rar) {
                return false;
            }

            $entries = $rar->getEntries();
            if (!$entries) {
                $rar->close();
                return false;
            }

            // Пробуем извлечь архив с каждым паролем
            foreach ($passwords as $password) {
                try {
                    $rar->setPassword($password);
                    foreach ($entries as $entry) {
                        $entry->extract($destination);
                    }
                    $rar->close();
                    return true;
                } catch (RarException) {
                    // Пробуем следующий пароль
                    continue;
                }
            }

            $rar->close();
        } catch (Exception) {
            return false;
        }

        return false;
    }
}
