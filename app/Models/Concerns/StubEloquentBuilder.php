<?php

declare(strict_types=1);

namespace App\Models\Concerns;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Collection;

/**
 * Builder-заглушка: не выполняет SQL, возвращает пустые результаты.
 */
class StubEloquentBuilder extends \Illuminate\Database\Eloquent\Builder
{
    public function get($columns = ['*']): \Illuminate\Database\Eloquent\Collection
    {
        return $this->model->newCollection();
    }

    public function first($columns = ['*']): ?Model
    {
        return null;
    }

    public function firstOrFail($columns = ['*']): Model
    {
        throw (new ModelNotFoundException)->setModel(get_class($this->model));
    }

    public function find($id, $columns = ['*']): ?Model
    {
        return null;
    }

    public function findOrFail($id, $columns = ['*']): Model
    {
        throw (new ModelNotFoundException)->setModel(get_class($this->model), [$id]);
    }

    public function value($column): mixed
    {
        return null;
    }

    public function pluck($column, $key = null): Collection
    {
        return new Collection();
    }

    public function count(): int
    {
        return 0;
    }

    public function exists(): bool
    {
        return false;
    }

    public function doesntExist(): bool
    {
        return true;
    }

    public function paginate($perPage = null, $columns = ['*'], $pageName = 'page', $page = null, $total = null): \Illuminate\Contracts\Pagination\LengthAwarePaginator
    {
        $page = $page ?: Paginator::resolveCurrentPage($pageName);
        $perPage = $perPage ?: $this->model->getPerPage();
        return new LengthAwarePaginator(
            $this->model->newCollection(),
            0,
            $perPage,
            $page,
            ['path' => Paginator::resolveCurrentPath(), 'pageName' => $pageName]
        );
    }

    public function simplePaginate($perPage = null, $columns = ['*'], $pageName = 'page', $page = null): \Illuminate\Contracts\Pagination\Paginator
    {
        $page = $page ?: Paginator::resolveCurrentPage($pageName);
        $perPage = $perPage ?: $this->model->getPerPage();
        return new Paginator($this->model->newCollection(), $perPage, $page, ['path' => Paginator::resolveCurrentPath(), 'pageName' => $pageName]);
    }

    public function cursor(): \Generator
    {
        yield from [];
    }

    public function chunkById($count, callable $callback, $column = null, $alias = null): bool
    {
        return true;
    }

    public function chunk($count, callable $callback): bool
    {
        return true;
    }

    public function avg($column): mixed
    {
        return null;
    }

    public function sum($column): mixed
    {
        return 0;
    }

    public function min($column): mixed
    {
        return null;
    }

    public function max($column): mixed
    {
        return null;
    }
}
