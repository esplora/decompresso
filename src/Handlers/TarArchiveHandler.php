<?php

namespace Esplora\Decompresso\Handlers;

use Esplora\Decompresso\Contracts\ArchiveInterface;
use PharData;
use Exception;

/**
 * Обработчик для работы с архивами формата TAR.
 *
 * Этот класс реализует интерфейс ArchiveInterface и предоставляет функциональность для извлечения TAR-архивов.
 */
class TarArchiveHandler implements ArchiveInterface
{
    /**
     * Извлекает содержимое TAR-архива в указанное место.
     *
     * @param string $filePath Путь к TAR-архиву, который нужно извлечь.
     * @param string $destination Каталог, в который будет извлечен архив. Если каталог не существует, он должен быть создан.
     * @param iterable $passwords Список паролей, не используется для TAR-архивов.
     * @return bool Возвращает true, если извлечение прошло успешно, и false в противном случае.
     */
    public function extract(string $filePath, string $destination, iterable $passwords = []): bool
    {
        try {
            $tar = new PharData($filePath);
            $tar->extractTo($destination);
            return true;
        } catch (Exception $e) {
            return false;
        }
    }
}
