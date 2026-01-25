<?php

namespace App\Services;

use App\Models\BlogCategory;
use App\Services\Contracts\BlogCategoryInterface;
use CoreConstants;
use Log;
use Str;
use Validator;

class BlogCategoryService implements BlogCategoryInterface
{
    /**
     * Eloquent instance
     *
     * @var BlogCategory
     */
    private $model;

    /**
     * Create a new service instance
     *
     * @param BlogCategory $category
     * @return void
     */
    public function __construct(BlogCategory $category)
    {
        $this->model = $category;
    }

    /**
     * Get all fields
     *
     * @param array $select
     * @return array
     */
    public function getAll(array $select = ['*'])
    {
        try {
            $result = $this->model->select($select)->get();

            if ($result) {
                return [
                    'message' => 'Data is fetched successfully',
                    'payload' => $result,
                    'status' => CoreConstants::STATUS_CODE_SUCCESS
                ];
            } else {
                return [
                    'message' => 'No result found',
                    'payload' => null,
                    'status' => CoreConstants::STATUS_CODE_NOT_FOUND
                ];
            }
        } catch (\Throwable $th) {
            Log::error($th->getMessage());
            return [
                'message' => 'Something went wrong',
                'payload' => $th->getMessage(),
                'status' => CoreConstants::STATUS_CODE_ERROR
            ];
        }
    }

    /**
     * Store/update data
     *
     * @param array $data
     * @return array
     */
    public function store(array $data)
    {
        try {
            $validate = Validator::make($data, [
                'name' => 'required|string',
            ]);

            if ($validate->fails()) {
                return [
                    'message' => 'Validation Error',
                    'payload' => $validate->errors(),
                    'status' => CoreConstants::STATUS_CODE_BAD_REQUEST
                ];
            }

            $slugSource = isset($data['slug']) && $data['slug'] !== '' ? $data['slug'] : $data['name'];
            $newData['name'] = $data['name'];
            $newData['slug'] = $this->makeUniqueSlug($slugSource, isset($data['id']) ? (int) $data['id'] : null);
            $newData['description'] = isset($data['description']) ? $data['description'] : null;
            $newData['is_active'] = isset($data['is_active']) ? $data['is_active'] : CoreConstants::TRUE;

            if (isset($data['id'])) {
                $result = $this->getById($data['id'], ['id']);
                if ($result['status'] !== CoreConstants::STATUS_CODE_SUCCESS) {
                    return $result;
                }

                $existingData = $result['payload'];
                $result = $existingData->update($newData);
            } else {
                $result = $this->model->create($newData);
            }

            if ($result) {
                return [
                    'message' => isset($data['id']) ? 'Data is successfully updated' : 'Data is successfully saved',
                    'payload' => $result,
                    'status' => CoreConstants::STATUS_CODE_SUCCESS
                ];
            }

            return [
                'message' => 'Something went wrong',
                'payload' => null,
                'status' => CoreConstants::STATUS_CODE_ERROR
            ];
        } catch (\Throwable $th) {
            Log::error($th->getMessage());
            return [
                'message' => 'Something went wrong',
                'payload' => $th->getMessage(),
                'status' => CoreConstants::STATUS_CODE_ERROR
            ];
        }
    }

    /**
     * Fetch item by id
     *
     * @param int $id
     * @param array $select
     * @return array
     */
    public function getById(int $id, array $select = ['*'])
    {
        try {
            $data = $this->model->select($select)->where('id', $id)->first();

            if ($data) {
                return [
                    'message' => 'Data is fetched successfully',
                    'payload' => $data,
                    'status' => CoreConstants::STATUS_CODE_SUCCESS
                ];
            }

            return [
                'message' => 'No result is found',
                'payload' => null,
                'status' => CoreConstants::STATUS_CODE_NOT_FOUND
            ];
        } catch (\Throwable $th) {
            Log::error($th->getMessage());
            return [
                'message' => 'Something went wrong',
                'payload' => $th->getMessage(),
                'status' => CoreConstants::STATUS_CODE_ERROR
            ];
        }
    }

    /**
     * Get all fields with paginate
     *
     * @param array $data
     * @param array $select
     * @return array
     */
    public function getAllWithPaginate(array $data, array $select = ['*'])
    {
        try {
            $perPage = !empty($data['params']) && !empty(json_decode($data['params'])->pageSize) ? json_decode($data['params'])->pageSize : 10;

            if (!empty($data['sorter']) && count(json_decode($data['sorter'], true))) {
                $sorter = json_decode($data['sorter'], true);
                foreach ($sorter as $key => $value) {
                    $sortBy = $key;
                    $sortType = ($value === 'ascend' ? 'asc' : 'desc');
                }
            } else {
                $sortBy = 'created_at';
                $sortType = 'desc';
            }

            $result = $this->model->select($select)->orderBy($sortBy, $sortType);

            if (!empty($data['params']) && !empty(json_decode($data['params'])->keyword) && json_decode($data['params'])->keyword !== '') {
                $searchQuery = json_decode($data['params'])->keyword;
                $columns = !empty($data['columns']) ? $data['columns'] : null;

                if ($columns) {
                    $result->where(function ($query) use ($columns, $searchQuery) {
                        foreach ($columns as $key => $column) {
                            if (!empty(json_decode($column)->search) && json_decode($column)->search === true) {
                                $fieldName = json_decode($column)->dataIndex;
                                $query->orWhere($fieldName, 'like', '%' . $searchQuery . '%');
                            }
                        }
                    });
                }
            }

            $result = $result->paginate($perPage);

            if ($result) {
                return [
                    'message' => 'Data is fetched successfully',
                    'payload' => $result,
                    'status' => CoreConstants::STATUS_CODE_SUCCESS
                ];
            }

            return [
                'message' => 'No result found',
                'payload' => null,
                'status' => CoreConstants::STATUS_CODE_NOT_FOUND
            ];
        } catch (\Throwable $th) {
            Log::error($th->getMessage());
            return [
                'message' => 'Something went wrong',
                'payload' => $th->getMessage(),
                'status' => CoreConstants::STATUS_CODE_ERROR
            ];
        }
    }

    /**
     * Delete items by id array
     *
     * @param array $ids
     * @return array
     */
    public function deleteByIds(array $ids)
    {
        try {
            $data = $this->model->whereIn('id', $ids)->delete();

            if ($data) {
                return [
                    'message' => 'Data is deleted successfully',
                    'payload' => $data,
                    'status' => CoreConstants::STATUS_CODE_SUCCESS
                ];
            }

            return [
                'message' => 'Nothing to Delete',
                'payload' => null,
                'status' => CoreConstants::STATUS_CODE_ERROR
            ];
        } catch (\Throwable $th) {
            Log::error($th->getMessage());
            return [
                'message' => 'Something went wrong',
                'payload' => $th->getMessage(),
                'status' => CoreConstants::STATUS_CODE_ERROR
            ];
        }
    }

    private function makeUniqueSlug(string $value, ?int $ignoreId = null)
    {
        $base = Str::slug($value);
        if ($base === '') {
            $base = Str::random(8);
        }

        $slug = $base;
        $counter = 1;
        while ($this->slugExists($slug, $ignoreId)) {
            $slug = $base . '-' . $counter;
            $counter++;
        }

        return $slug;
    }

    private function slugExists(string $slug, ?int $ignoreId = null)
    {
        $query = $this->model->newQuery()->where('slug', $slug);
        if ($ignoreId) {
            $query->where('id', '<>', $ignoreId);
        }

        return $query->exists();
    }
}
