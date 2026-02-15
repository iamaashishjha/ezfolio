<?php

namespace App\Services;

use App\Models\BlogPost;
use App\Services\Contracts\BlogPostInterface;
use Carbon\Carbon;
use CoreConstants;
use Illuminate\Http\UploadedFile;
use Log;
use Str;
use Validator;

class BlogPostService implements BlogPostInterface
{
    /**
     * Eloquent instance
     *
     * @var BlogPost
     */
    private $model;

    /**
     * @var MediaStorageService
     */
    private $mediaStorage;

    /**
     * Create a new service instance
     *
     * @param BlogPost $post
     * @return void
     */
    public function __construct(BlogPost $post, MediaStorageService $mediaStorage)
    {
        $this->model = $post;
        $this->mediaStorage = $mediaStorage;
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
                ->with(['category', 'tags'])
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
            $rules = [
                'title' => 'required|string',
                'category_id' => 'required',
                'body' => 'required|string',
            ];

            if (!$isUpdate) {
                $rules['cover_image'] = 'required';
            }

            $validate = Validator::make($data, $rules);

            if ($validate->fails()) {
                return [
                    'message' => 'Validation Error',
                    'payload' => $validate->errors(),
                    'status' => CoreConstants::STATUS_CODE_BAD_REQUEST
                ];
            }

            $slugSource = isset($data['slug']) && $data['slug'] !== '' ? $data['slug'] : $data['title'];

            $newData['category_id'] = $data['category_id'];
            $newData['title'] = $data['title'];
            $newData['slug'] = $this->makeUniqueSlug($slugSource, $isUpdate ? (int) $data['id'] : null);
            $newData['excerpt'] = isset($data['excerpt']) ? $data['excerpt'] : null;
            $newData['body'] = $data['body'];
            $newData['status'] = isset($data['status']) ? $data['status'] : 'draft';
            $newData['allow_comments'] = isset($data['allow_comments']) ? $data['allow_comments'] : CoreConstants::TRUE;
            $newData['meta_title'] = isset($data['meta_title']) ? $data['meta_title'] : null;
            $newData['meta_description'] = isset($data['meta_description']) ? $data['meta_description'] : null;
            $newData['meta_keywords'] = isset($data['meta_keywords']) ? $data['meta_keywords'] : null;
            $newData['canonical_url'] = isset($data['canonical_url']) ? $data['canonical_url'] : null;

            if (!empty($data['published_at'])) {
                $newData['published_at'] = $data['published_at'];
            }

            if ($newData['status'] === 'published' && empty($newData['published_at'])) {
                $newData['published_at'] = Carbon::now()->format('Y-m-d H:i:s');
            }

            $existingData = null;
            if ($isUpdate) {
                $result = $this->getById($data['id'], ['id', 'cover_image']);
                if ($result['status'] !== CoreConstants::STATUS_CODE_SUCCESS) {
                    return $result;
                }
                $existingData = $result['payload'];
            }

            if (isset($data['cover_image']) && ($data['cover_image'] instanceof UploadedFile)) {
                $coverResponse = $this->processCoverImage($data['cover_image'], $existingData);
                if ($coverResponse['status'] !== CoreConstants::STATUS_CODE_SUCCESS) {
                    return $coverResponse;
                }
                $newData['cover_image'] = $coverResponse['payload']['file'];
            } elseif (!$isUpdate && empty($data['cover_image'])) {
                return [
                    'message' => 'Validation Error',
                    'payload' => ['cover_image' => ['Cover image is required.']],
                    'status' => CoreConstants::STATUS_CODE_BAD_REQUEST
                ];
            }

            if ($isUpdate) {
                $result = $existingData->update($newData);
                $post = $existingData;
            } else {
                $post = $this->model->create($newData);
                $result = $post;
            }

            if ($result) {
                if (!empty($newData['cover_image'])) {
                    $this->mediaStorage->attachToOwner($newData['cover_image'], $post, 'cover_image');
                }

                $tagIds = $this->normalizeTagIds($data);
                $post->tags()->sync($tagIds);

                return [
                    'message' => $isUpdate ? 'Data is successfully updated' : 'Data is successfully saved',
                    'payload' => $post->load(['category', 'tags']),
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
                ->with(['category', 'tags'])
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
     * Fetch item by slug
     *
     * @param string $slug
     * @param array $select
     * @return array
     */
    public function getBySlug(string $slug, array $select = ['*'])
    {
        try {
            $data = $this->model
                ->with(['category', 'tags'])
                ->select($select)
                ->where('slug', $slug)
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
                ->with(['category', 'tags'])
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

            if (!empty($data['params']) && !empty(json_decode($data['params'])->status) && json_decode($data['params'])->status !== '') {
                $result->where('status', json_decode($data['params'])->status);
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
     * Get published posts for frontend with filters
     *
     * @param array $filters
     * @return array
     */
    public function getFrontendPaginated(array $filters = [])
    {
        try {
            $perPage = !empty($filters['perPage']) ? (int) $filters['perPage'] : 6;
            $search = !empty($filters['search']) ? $filters['search'] : null;
            $category = !empty($filters['category']) ? $filters['category'] : null;
            $tag = !empty($filters['tag']) ? $filters['tag'] : null;

            $query = $this->model
                ->with(['category', 'tags'])
                ->withCount(['comments' => function ($q) {
                    $q->where('is_approved', CoreConstants::TRUE);
                }])
                ->where('status', 'published')
                ->where(function ($q) {
                    $q->whereNull('published_at')
                        ->orWhere('published_at', '<=', Carbon::now()->format('Y-m-d H:i:s'));
                });

            if ($category) {
                $query->whereHas('category', function ($q) use ($category) {
                    $q->where('slug', $category);
                });
            }

            if ($tag) {
                $query->whereHas('tags', function ($q) use ($tag) {
                    $q->where('slug', $tag);
                });
            }

            if ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('title', 'like', '%' . $search . '%')
                        ->orWhere('excerpt', 'like', '%' . $search . '%')
                        ->orWhere('body', 'like', '%' . $search . '%');
                });
            }

            $result = $query
                ->orderBy('published_at', 'desc')
                ->orderBy('created_at', 'desc')
                ->paginate($perPage);

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
     * Fetch published post by slug
     *
     * @param string $slug
     * @return array
     */
    public function getFrontendBySlug(string $slug)
    {
        try {
            $data = $this->model
                ->with(['category', 'tags'])
                ->where('slug', $slug)
                ->where('status', 'published')
                ->where(function ($q) {
                    $q->whereNull('published_at')
                        ->orWhere('published_at', '<=', Carbon::now()->format('Y-m-d H:i:s'));
                })
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
     * Delete items by id array
     *
     * @param array $ids
     * @return array
     */
    public function deleteByIds(array $ids)
    {
        try {
            $posts = $this->model->whereIn('id', $ids)->get(['id', 'cover_image']);
            foreach ($posts as $post) {
                $this->mediaStorage->deleteByPath($post->cover_image);
            }

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

    private function processCoverImage(UploadedFile $file, $post = null)
    {
        if ($post && !empty($post->cover_image)) {
            $this->mediaStorage->deleteByPath($post->cover_image);
        }

        try {
            $response = $this->mediaStorage->upload($file, 'blog/covers', [
                'owner' => $post,
                'collection' => 'cover_image',
            ]);

            if ($response['status'] !== CoreConstants::STATUS_CODE_SUCCESS) {
                return $response;
            }

            return [
                'message' => 'File is successfully saved',
                'payload' => [
                    'file' => $response['payload']['path']
                ],
                'status' => CoreConstants::STATUS_CODE_SUCCESS
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

    private function normalizeTagIds(array $data)
    {
        if (empty($data['tags'])) {
            return [];
        }

        if (is_array($data['tags'])) {
            return array_values(array_filter($data['tags'], function ($value) {
                return $value !== null && $value !== '';
            }));
        }

        $decoded = json_decode($data['tags'], true);
        if (is_array($decoded)) {
            return array_values(array_filter($decoded, function ($value) {
                return $value !== null && $value !== '';
            }));
        }

        return [];
    }
}
