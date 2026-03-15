<?php

declare(strict_types=1);

namespace Src\Shared\Contracts;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

interface BaseRepositoryInterface
{
    public function findById($id): ?Model;

    public function findOneBy(array $filters): ?Model;

    public function findMany(array $filters = array(), array $sort = array()): Collection;

    public function paginate(int $perPage = 15, array $filters = array(), array $sort = array()): LengthAwarePaginator;

    public function create(array $data): Model;

    public function update($id, array $data): ?Model;

    public function delete($id): bool;
}
