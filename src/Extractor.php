<?php

namespace Esplora\Decompresso;

use Esplora\Decompresso\Contracts\ArchiveInterface;
use Esplora\Decompresso\Contracts\PasswordProviderInterface;
use Esplora\Decompresso\Providers\ArrayPasswordProvider;
use RuntimeException;

/**
 * Класс для извлечения архивов с поддержкой паролей и обработчиков архива.
 *
 * Этот класс управляет процессом извлечения архивов, поддерживая работу с разными типами архивов через
 * обработчики, а также использование различных паролей через провайдер паролей.
 */
class Extractor
{
    /**
     * Провайдер паролей, используемый для попытки извлечения защищенных архива.
     *
     * @var PasswordProviderInterface
     */
    protected PasswordProviderInterface $passwordProvider;

    /**
     * Массив обработчиков архивов, которые могут извлекать архивы.
     *
     * @var ArchiveInterface[]
     */
    protected array $archiveHandlers = [];

    /**
     * Обработчик для случая неудачного извлечения архива.
     *
     * @var callable
     */
    protected $failureCallback;

    /**
     * Обработчик для случая успешного извлечения архива.
     *
     * @var callable
     */
    protected $successCallback;

    /**
     * Конструктор класса Extractor.
     *
     * Инициализирует обработчики по умолчанию для случаев успешного и неудачного извлечения архива.
     */
    public function __construct()
    {
        // По умолчанию использует провайдер паролей с пустым списком паролей.
        $this->passwordProvider = new ArrayPasswordProvider([]);

        // По умолчанию выбрасывает исключение при неудачном извлечении архива.
        $this->failureCallback = fn (string $filePath) => throw new \Exception("Не удалось извлечь архив: {$filePath}");

        // По умолчанию возвращает true при успешном извлечении архива.
        $this->successCallback = fn () => true;
    }

    /**
     * Устанавливает провайдер паролей для использования при извлечении защищенных архивов.
     *
     * @param PasswordProviderInterface $provider Провайдер паролей, который будет предоставлять пароли.
     *
     * @return $this Возвращает текущий экземпляр для цепочки вызовов.
     */
    public function withPasswords(PasswordProviderInterface $provider): self
    {
        $this->passwordProvider = $provider;

        return $this;
    }

    /**
     * Добавляет обработчик архива для поддержки различных форматов архивов.
     *
     * @param ArchiveInterface $handler Обработчик архива, который может извлекать архивы.
     *
     * @return $this Возвращает текущий экземпляр для цепочки вызовов.
     */
    public function withHandler(ArchiveInterface $handler): self
    {
        $this->archiveHandlers[] = $handler;

        return $this;
    }

    /**
     * Устанавливает обработчик, который будет вызван в случае неудачного извлечения архива.
     *
     * @param callable $callback Обработчик для обработки неудачного извлечения архива.
     *
     * @return $this Возвращает текущий экземпляр для цепочки вызовов.
     */
    public function onFailure(callable $callback): self
    {
        $this->failureCallback = $callback;

        return $this;
    }

    /**
     * Устанавливает обработчик, который будет вызван в случае успешного извлечения архива.
     *
     * @param callable $callback Обработчик для обработки успешного извлечения архива.
     *
     * @return $this Возвращает текущий экземпляр для цепочки вызовов.
     */
    public function onSuccess(callable $callback): self
    {
        $this->successCallback = $callback;

        return $this;
    }

    /**
     * Извлекает архив в указанное место.
     *
     * Этот метод пробует извлечь архив, находящийся по указанному пути, в указанный каталог.
     * Использует добавленные обработчики архива для извлечения.
     * Если извлечение не удается, вызывается обработчик неудачного извлечения.
     * В противном случае вызывается обработчик успешного извлечения.
     *
     * @param string      $filePath    Путь к архиву, который нужно извлечь.
     * @param string|null $destination Каталог, в который будет извлечен архив. Если не указан, используется каталог
     *                                 с тем же именем, что и архив.
     *
     * @throws \Exception Если провайдер паролей не установлен, будет выброшено исключение.
     *
     * @return mixed Возвращает результат выполнения обработчика на случай успешного извлечения архива.
     */
    public function extract(string $filePath, ?string $destination = null): mixed
    {
        $destination = $destination ?: dirname($filePath);
        $success = false;

        // Создаём директорию назначения, если она не существует
        $this->ensureDirectoryExists($destination);

        // Попытка извлечения архива с использованием всех добавленных обработчиков.
        foreach ($this->archiveHandlers as $handler) {
            if ($handler->extract($filePath, $destination, $this->passwordProvider->getPasswords())) {
                $success = true;
                break;
            }
        }

        $callback = $success ? $this->successCallback : $this->failureCallback;

        // Вызов соответствующего обработчика в зависимости от результата извлечения.
        return $callback($filePath, $destination);
    }

    /**
     * Убедитесь, что директория существует. Если нет, создайте её.
     *
     * @param string $directory Путь к директории.
     *
     * @throws RuntimeException Если директорию не удалось создать.
     */
    protected function ensureDirectoryExists(string $directory): void
    {
        if (! is_dir($directory) && ! mkdir($directory, 0777, true) && ! is_dir($directory)) {
            throw new RuntimeException("Не удалось создать директорию: {$directory}");
        }
    }
}
