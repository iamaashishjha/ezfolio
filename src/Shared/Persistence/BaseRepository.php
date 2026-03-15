<?php

declare(strict_types=1);

namespace Src\Shared\Persistence;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Src\Shared\Contracts\BaseRepositoryInterface;

abstract class BaseRepository implements BaseRepositoryInterface
{
    protected Model $model;

    public function __construct(Model $model)
    {
        $this->model = $model;
    }

    public function findById($id): ?Model
    {
        return $this->newQuery()->find($id);
    }

    public function findOneBy(array $filters): ?Model
    {
        return $this->applyFilters($this->newQuery(), $filters)->first();
    }

    public function findMany(array $filters = array(), array $sort = array()): Collection
    {
        $query = $this->applyFilters($this->newQuery(), $filters);
        return $this->applySort($query, $sort)->get();
    }

    public function paginate(int $perPage = 15, array $filters = array(), array $sort = array()): LengthAwarePaginator
    {
        $query = $this->applyFilters($this->newQuery(), $filters);
        return $this->applySort($query, $sort)->paginate($perPage);
    }

    public function create(array $data): Model
    {
        return $this->newQuery()->create($data);
    }

    public function update($id, array $data): ?Model
    {
        $model = $this->findById($id);
        if ($model === null) {
            return null;
        }

        $model->fill($data);
        $model->save();

        return $model->refresh();
    }

    public function delete($id): bool
    {
        $model = $this->findById($id);
        if ($model === null) {
            return false;
        }

        return (bool) $model->delete();
    }

    protected function newQuery()
    {
        return $this->model->newQuery();
    }

    protected function applyFilters($query, array $filters)
    {
        foreach ($filters as $field => $value) {
            if ($value !== null) {
                $query->where($field, $value);
            }
        }

        return $query;
    }

    protected function applySort($query, array $sort)
    {
        foreach ($sort as $field => $direction) {
            $query->orderBy($field, strtolower((string) $direction) === 'desc' ? 'desc' : 'asc');
        }

        return $query;
    }
}
