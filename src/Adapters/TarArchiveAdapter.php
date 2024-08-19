<?php

namespace Esplora\Decompresso\Adapters;

use Esplora\Decompresso\Concerns\SupportsMimeTypes;
use Esplora\Decompresso\Contracts\ArchiveInterface;
use Esplora\Decompresso\Contracts\PasswordProviderInterface;
use PharData;

/**
 * Обработчик для работы с архивами формата TAR.
 *
 * Этот класс реализует интерфейс ArchiveInterface и предоставляет функциональность для извлечения TAR-архивов.
 */
class TarArchiveAdapter implements ArchiveInterface
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
            'application/x-tar',
        ];
    }

    /**
     * Извлекает содержимое TAR-архива в указанное место.
     *
     * @param string                    $filePath    Путь к TAR-архиву, который нужно извлечь.
     * @param string                    $destination Каталог, в который будет извлечен архив. Если каталог не существует, он должен быть создан.
     * @param PasswordProviderInterface $passwords   Список паролей, не используется для TAR-архивов.
     *
     * @return bool Возвращает true, если извлечение прошло успешно, и false в противном случае.
     */
    public function extract(string $filePath, string $destination, PasswordProviderInterface $passwords): bool
    {
        $tar = new PharData($filePath);
        $tar->extractTo($destination);

        return true;
    }
}