<?php

namespace Esplora\Lumos\Contracts;

use Illuminate\Support\Collection;

interface SummaryInterface
{
    /**
     * Добавить шаг в отчет.
     *
     * @param bool  $success Успешность текущего шага.
     * @param array $context Контекст текущего шага.
     *
     * @return static
     */
    public function addStep(bool $success, array $context = []): static;

    /**
     * Получить все шаги отчета.
     *
     * @return Collection
     */
    public function steps(): Collection;

    /**
     * Проверить успешность всего процесса.
     *
     * @return bool
     */
    public function isSuccessful(): bool;

    /**
     * Получить количество попыток.
     *
     * @return int
     */
    public function attempts(): int;
}
