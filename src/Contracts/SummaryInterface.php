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
     */
    public function addStep(bool $success, array $context = []): static;

    /**
     * Получить все шаги отчета.
     */
    public function steps(): Collection;

    /**
     * Проверить успешность всего процесса.
     */
    public function isSuccessful(): bool;

    /**
     * Получить количество попыток.
     */
    public function attempts(): int;
}
