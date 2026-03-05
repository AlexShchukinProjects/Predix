<?php

declare(strict_types=1);

namespace App\Models\Concerns;

use Illuminate\Database\Eloquent\Builder as EloquentBuilder;

/**
 * Заглушка для моделей модуля Надежность: запросы к БД не выполняются,
 * чтение возвращает пустые результаты, запись не сохраняется.
 */
trait StubRelModel
{
    public function newEloquentBuilder($query): EloquentBuilder
    {
        return new StubEloquentBuilder($query);
    }

    public function save(array $options = []): bool
    {
        $this->exists = true;
        if (empty($this->id)) {
            $this->id = 0;
        }
        return true;
    }

    public function delete(): ?bool
    {
        $this->exists = false;
        return true;
    }
}
