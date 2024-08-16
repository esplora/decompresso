<?php

namespace Esplora\Decompresso\Concerns;

use Symfony\Component\Mime\MimeTypes;

trait SupportsMimeTypes
{
    /**
     * Проверяет, поддерживает ли обработчик данный формат архива, используя Symfony Mime.
     *
     * @param string $filePath Путь к архиву, который нужно проверить.
     *
     * @return bool Возвращает true, если формат архива поддерживается, и false в противном случае.
     */
    public function canSupport(string $filePath): bool
    {
        $fileMimeType = (new MimeTypes)->guessMimeType($filePath);

        return in_array($fileMimeType, $this->supportedMimeTypes(), true);
    }

    /**
     * Возвращает список поддерживаемых MIME-типов.
     *
     * @return array<string> Массив MIME-типов, которые поддерживает обработчик.
     */
    protected function supportedMimeTypes(): array
    {
        return [
            // Заглушка, которая должна быть переопределена в конкретных обработчиках.
        ];
    }
}
