<?php

namespace Esplora\Decompresso\Providers;

use Esplora\Decompresso\Contracts\PasswordProviderInterface;

/**
 * Провайдер паролей, который возвращает пароли из массива.
 *
 * Этот класс реализует интерфейс PasswordProviderInterface и предоставляет пароли для извлечения защищенных архива
 * из массива, переданного в конструкторе. Он подходит для простых случаев, когда список паролей известен заранее
 * и не меняется динамически.
 */
class ArrayPasswordProvider implements PasswordProviderInterface
{
    /**
     * Конструктор класса ArrayPasswordProvider.
     *
     * @param array $passwords Массив паролей, которые будут использоваться для извлечения защищенных архивов.
     */
    public function __construct(protected array $passwords)
    {
    }

    /**
     * Возвращает список паролей.
     *
     * Метод возвращает массив паролей, переданных в конструкторе. Эти пароли могут использоваться для попытки
     * извлечения защищенных паролем архивов.
     *
     * @return array Возвращает array, содержащий строки паролей.
     */
    public function getPasswords(): array
    {
        return $this->passwords;
    }
}
