<?php

namespace Esplora\Decompresso\Handlers;

use Esplora\Decompresso\Concerns\SupportsMimeTypes;
use Esplora\Decompresso\Contracts\ArchiveInterface;
use Exception;
use RarArchive;

/**
 * Обработчик для работы с архивами формата RAR.
 *
 * Этот класс реализует интерфейс ArchiveInterface и предоставляет функциональность для извлечения RAR-архивов,
 * включая поддержку паролей для защищенных архивов.
 */
class RarArchiveHandler implements ArchiveInterface
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
            'application/vnd.rar',
            'application/x-rar-compressed',
        ];
    }

    /**
     * Извлекает содержимое RAR-архива в указанное место.
     *
     * @param string   $filePath    Путь к RAR-архиву, который нужно извлечь.
     * @param string   $destination Каталог, в который будет извлечен архив. Если каталог не существует, он должен быть создан.
     * @param iterable $passwords   Список паролей для попытки извлечения защищенного паролем архива. Может быть массивом или другим iterable объектом.
     *
     * @return bool Возвращает true, если извлечение прошло успешно, и false в противном случае.
     */
    public function extract(string $filePath, string $destination, iterable $passwords = []): bool
    {
        $rar = RarArchive::open($filePath);

        if (! $rar) {
            // Точно ли нужно? Сомниваюсь, что это правильно TODO: <--- Проверить
            return false;
        }

        $entries = $rar->getEntries();

        if ($entries === false) {
            $rar->close();
        }

        if ($entries !== false) {
            foreach ($entries as $entry) {
                $entry->extract($destination);
            }
            $rar->close();

            return true;
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
            } catch (Exception) {
                // Пробуем следующий пароль
                continue;
            }
        }

        return false;
    }
}
