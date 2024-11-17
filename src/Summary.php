<?php

namespace Esplora\Lumos;

use Esplora\Lumos\Contracts\SummaryInterface;
use Illuminate\Support\Collection;
use Symfony\Component\Process\Process;

class Summary implements SummaryInterface
{
    /**
     * Успешность извлечения.
     *
     * @var bool
     */
    protected bool $success = false;

    /**
     * Количество попыток извлечения.
     *
     * @var int
     */
    protected int $attempts = 0;

    /**
     * Шаги извлечения.
     *
     * @var \Illuminate\Support\Collection
     */
    protected Collection $steps;

    /**
     * @return void
     */
    public function __construct()
    {
        $this->steps = collect();
    }

    /**
     * Добавляет шаг в отчет.
     *
     * @param bool  $success Успешность текущего шага.
     * @param array $context Контекст текущего шага.
     *
     * @return $this For method chaining.
     */
    public function addStep(bool $success, array $context = []): static
    {
        if ($success) {
            $this->success = true;
        }

        $this->attempts++;

        $contextHash = $this->hashContext($success, $context);

        // Добавляем только уникальные шаги
        if ($this->steps->has($contextHash)) {
            return $this;
        }

        $this->steps->put($contextHash, [
            'success' => $success,
            'context' => $context,
        ]);

        return $this;
    }

    /**
     * Генерирует уникальный хеш для шага.
     *
     * @param bool  $success Успешность шага.
     * @param array $context Контекст шага.
     *
     * @return string
     */
    protected function hashContext(bool $success, array $context): string
    {
        return sha1(json_encode([
            'success' => $success,
            'context' => $context,
        ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
    }

    /**
     * Возвращает все шаги отчета.
     *
     * @return Collection
     */
    public function steps(): Collection
    {
        return $this->steps;
    }

    /**
     * Проверяет успешность всего процесса.
     *
     * @return bool
     */
    public function isSuccessful(): bool
    {
        return $this->success;
    }

    /**
     * Получить количество попыток.
     *
     * @return int
     */
    public function attempts(): int
    {
        return $this->attempts++;
    }

    /**
     * @param bool                               $success
     * @param \Symfony\Component\Process\Process $process
     * @param string|null                        $password
     *
     * @return $this For method chaining.
     */
    public function addStepWithProcess(bool $success, Process $process, ?string $password = null): static
    {
        return $this->addStep($success, [
            'isSuccessful' => $process->isSuccessful(),
            'output'       => $process->getOutput(),
            'error'        => $process->getErrorOutput(),
            'exitCode'     => $process->getExitCode(),
            'exitCodeText' => $process->getExitCodeText(),
            'password'     => $password,
        ]);
    }
}
