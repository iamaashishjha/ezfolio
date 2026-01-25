<?php

namespace App\Services;

use App\Models\BlogComment;
use App\Services\Contracts\BlogCommentInterface;
use CoreConstants;
use Log;
use Validator;

class BlogCommentService implements BlogCommentInterface
{
    /**
     * Eloquent instance
     *
     * @var BlogComment
     */
    private $model;

    /**
     * Create a new service instance
     *
     * @param BlogComment $comment
     * @return void
     */
    public function __construct(BlogComment $comment)
    {
        $this->model = $comment;
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
            $result = $this->model
                ->with(['post'])
                ->select($select)
                ->get();

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
     * Store/update data
     *
     * @param array $data
     * @return array
     */
    public function store(array $data)
    {
        try {
            $isUpdate = isset($data['id']);

            if (!$isUpdate) {
                $validate = Validator::make($data, [
                    'blog_post_id' => 'required',
                    'name' => 'required|string',
                    'body' => 'required|string',
                ]);

                if ($validate->fails()) {
                    return [
                        'message' => 'Validation Error',
                        'payload' => $validate->errors(),
                        'status' => CoreConstants::STATUS_CODE_BAD_REQUEST
                    ];
                }

                $newData['blog_post_id'] = $data['blog_post_id'];
                $newData['parent_id'] = isset($data['parent_id']) ? $data['parent_id'] : null;
                $newData['name'] = $data['name'];
                $newData['email'] = isset($data['email']) ? $data['email'] : null;
                $newData['body'] = $data['body'];
                $newData['is_approved'] = isset($data['is_approved']) ? $data['is_approved'] : CoreConstants::FALSE;

                if (!empty($newData['parent_id'])) {
                    $parent = $this->model
                        ->where('id', $newData['parent_id'])
                        ->where('blog_post_id', $newData['blog_post_id'])
                        ->first();
                    if (!$parent) {
                        return [
                            'message' => 'Invalid parent comment',
                            'payload' => null,
                            'status' => CoreConstants::STATUS_CODE_BAD_REQUEST
                        ];
                    }
                }

                $result = $this->model->create($newData);
            } else {
                $result = $this->getById($data['id'], ['id']);
                if ($result['status'] !== CoreConstants::STATUS_CODE_SUCCESS) {
                    return $result;
                }
                $existingData = $result['payload'];

                $newData = [];
                if (isset($data['is_approved'])) {
                    $newData['is_approved'] = $data['is_approved'];
                }
                if (isset($data['name'])) {
                    $newData['name'] = $data['name'];
                }
                if (isset($data['email'])) {
                    $newData['email'] = $data['email'];
                }
                if (isset($data['body'])) {
                    $newData['body'] = $data['body'];
                }
                if (isset($data['parent_id'])) {
                    $newData['parent_id'] = $data['parent_id'];
                }

                $result = $existingData->update($newData);
            }

            if ($result) {
                return [
                    'message' => $isUpdate ? 'Data is successfully updated' : 'Data is successfully saved',
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
            $data = $this->model
                ->with(['post'])
                ->select($select)
                ->where('id', $id)
                ->first();

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

            $result = $this->model
                ->with(['post'])
                ->select($select)
                ->orderBy($sortBy, $sortType);

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

            if (!empty($data['params']) && isset(json_decode($data['params'])->is_approved) && json_decode($data['params'])->is_approved !== '') {
                $result->where('is_approved', json_decode($data['params'])->is_approved);
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
     * Get approved comments by post id
     *
     * @param int $postId
     * @return array
     */
    public function getApprovedByPostId(int $postId)
    {
        try {
            $data = $this->model
                ->select(['*'])
                ->where('blog_post_id', $postId)
                ->where('is_approved', CoreConstants::TRUE)
                ->orderBy('created_at', 'asc')
                ->get();

            if ($data) {
                return [
                    'message' => 'Data is fetched successfully',
                    'payload' => $data,
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
}
