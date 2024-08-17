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
    protected array $adapters = [];

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
     * Обработчик, вызываемый, если пароль не был подобран.
     *
     * @var callable
     */
    protected $passwordFailureCallback;

    /**
     * Конструктор класса Extractor.
     *
     * Инициализирует обработчики по умолчанию для случаев успешного и неудачного извлечения архива.
     */
    public function __construct()
    {
        // По умолчанию использует провайдер паролей с пустым списком паролей.
        $this->passwordProvider = new ArrayPasswordProvider([]);

        // По умолчанию возвращает false при не успешном извлечении архива по вине ПО.
        $this->failureCallback = fn (\Throwable $exception) => false;

        // По умолчанию возвращает true при успешном извлечении архива.
        $this->successCallback = fn () => true;

        // По умолчанию возвращает false при не успешном извлечении архива.
        $this->passwordFailureCallback = fn () => false;
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
    public function withAdapter(ArchiveInterface $handler): self
    {
        $this->adapters[] = $handler;

        return $this;
    }

    /**
     * Добавляет несколько обработчиков архивов.
     *
     * @param ArchiveInterface[] $handlers Массив обработчиков архивов.
     *
     * @return $this Возвращает текущий экземпляр для цепочки вызовов.
     */
    public function withAdapters(array $handlers): self
    {
        foreach ($handlers as $handler) {
            $this->withAdapter($handler);
        }

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
     * Устанавливает обработчик, если пароль не был подобран.
     *
     * @param callable $callback Обработчик неудачи из-за неподобранного пароля.
     *
     * @return $this
     */
    public function onPasswordFailure(callable $callback): self
    {
        $this->passwordFailureCallback = $callback;

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
     * Этот метод вызывает основной процесс извлечения в блоке try/catch для обработки исключений.
     * Если извлечение не удается из-за неподобранного пароля, вызывается соответствующий обработчик.
     * Если выбрасывается исключение, вызывается обработчик ошибок.
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
        try {
            $success = $this->performExtraction($filePath, $destination);
        } catch (\Throwable $throwable) {
            return call_user_func($this->failureCallback, $throwable, $filePath, $destination);
        }

        $callback = $success ? $this->successCallback : $this->passwordFailureCallback;

        // Вызов соответствующего обработчика в зависимости от результата извлечения.
        return $callback($filePath, $destination);
    }

    /**
     * Выполняет извлечение архива.
     *
     * Этот метод использует все добавленные обработчики для извлечения архива и вызывает соответствующий обработчик
     * в зависимости от результата.
     *
     * @param string      $filePath    Путь к архиву, который нужно извлечь.
     * @param string|null $destination Каталог, в который будет извлечен архив. Если не указан, используется каталог
     *                                 с тем же именем, что и архив.
     *
     * @return bool Результат вызова обработчика успешного извлечения или обработчика неудачи.
     */
    private function performExtraction(string $filePath, ?string $destination = null): bool
    {
        $destination = $destination ?: dirname($filePath);

        // Создаём директорию назначения, если она не существует
        // $this->ensureDirectoryExists($destination);

        $supportHandlers = array_filter($this->adapters, fn (ArchiveInterface $archive) => $archive->canSupport($filePath));

        // Попытка извлечения архива с использованием всех добавленных обработчиков.
        foreach ($supportHandlers as $handler) {
            if ($handler->extract($filePath, $destination, $this->passwordProvider)) {
                return true;
            }
        }

        return false;
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
