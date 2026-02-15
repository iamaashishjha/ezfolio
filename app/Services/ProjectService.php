<?php

namespace App\Services;

use CoreConstants;
use App\Models\Project;
use App\Services\Contracts\ProjectInterface;
use Illuminate\Http\UploadedFile;
use Log;
use Validator;

class ProjectService implements ProjectInterface
{
    /**
     * Eloquent instance
     *
     * @var Project
     */
    private $model;

    /**
     * @var MediaStorageService
     */
    private $mediaStorage;

    /**
     * Create a new service instance
     *
     * @param Project $project
     * @return void
     */
    public function __construct(Project $project, MediaStorageService $mediaStorage)
    {
        $this->model = $project;
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
            if (isset($data['seeder_thumbnail']) && isset($data['seeder_images'])) {
                $validate = Validator::make($data, [
                    'title' => 'required|string',
                    'categories' => 'required'
                ]);
            } else {
                $validate = Validator::make($data, [
                    'title' => 'required|string',
                    'thumbnail' => 'required',
                    'images' => 'required',
                    'categories' => 'required'
                ]);
            }

            if ($validate->fails()) {
                return [
                    'message' => 'Validation Error',
                    'payload' => $validate->errors(),
                    'status' => CoreConstants::STATUS_CODE_BAD_REQUEST
                ];
            }

            $newData['title'] = $data['title'];
            $newData['categories'] = json_encode($data['categories']);
            $newData['link'] = isset($data['link']) ? $data['link'] : null;
            $newData['details'] = isset($data['details']) ? $data['details'] : null;

            $project = null;
            if (isset($data['seeder_thumbnail']) && isset($data['seeder_images'])) {
                $newData['thumbnail'] = $data['seeder_thumbnail'];
                $newData['images'] = json_encode($data['seeder_images']);
                $project = $this->model->create($newData);
                $result = $project;
            } else {
                if (!empty($data['id'])) {
                    $result = $this->getById($data['id'], ['*']);
                    if ($result['status'] !== CoreConstants::STATUS_CODE_SUCCESS) {
                        return $result;
                    } else {
                        $existingData = $result['payload'];
                    }

                    //process thumbnail
                    $processThumbnail = $this->processThumbnail($data['thumbnail'], $existingData);
                    if ($processThumbnail['status'] !== CoreConstants::STATUS_CODE_SUCCESS) {
                        return $processThumbnail;
                    }
                    //process images
                    $processImages = $this->processImages($data['images'], $existingData);
                    if ($processImages['status'] !== CoreConstants::STATUS_CODE_SUCCESS) {
                        return $processImages;
                    }
                    $newData['thumbnail'] = $processThumbnail['payload']['file'];
                    $newData['images'] = json_encode($processImages['payload']['files']);

                    $result = $existingData->update($newData);
                    $project = $existingData;
                } else {
                    //process thumbnail
                    $processThumbnail = $this->processThumbnail($data['thumbnail']);
                    if ($processThumbnail['status'] !== CoreConstants::STATUS_CODE_SUCCESS) {
                        return $processThumbnail;
                    }

                    //process images
                    $processImages = $this->processImages($data['images']);
                    if ($processImages['status'] !== CoreConstants::STATUS_CODE_SUCCESS) {
                        return $processImages;
                    }

                    $newData['thumbnail'] = $processThumbnail['payload']['file'];
                    $newData['images'] = json_encode($processImages['payload']['files']);
                    $project = $this->model->create($newData);
                    $result = $project;
                }

                if ($project && !empty($newData['thumbnail'])) {
                    $this->mediaStorage->attachToOwner($newData['thumbnail'], $project, 'thumbnail');
                }

                if ($project && !empty($newData['images'])) {
                    $images = json_decode($newData['images'], true);
                    if (is_array($images)) {
                        foreach ($images as $path) {
                            $this->mediaStorage->attachToOwner($path, $project, 'images');
                        }
                    }
                }
            }

            if ($result) {
                return [
                    'message' => isset($data['id']) ? 'Data is successfully updated' : 'Data is successfully saved',
                    'payload' => $result,
                    'status' => CoreConstants::STATUS_CODE_SUCCESS
                ];
            } else {
                return [
                    'message' => 'Something went wrong',
                    'payload' => null,
                    'status' => CoreConstants::STATUS_CODE_ERROR
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
     * Process the thumbnail
     *
     * @param UploadedFile $file
     * @param Project|null $project
     * @return array
     */
    private function processThumbnail(UploadedFile $file, $project = null)
    {
        try {
            $oldPath = $project ? $project->thumbnail : null;
            $upload = $this->mediaStorage->upload($file, 'projects/thumbnail', [
                'owner' => $project,
                'collection' => 'thumbnail',
            ]);
            if ($upload['status'] !== CoreConstants::STATUS_CODE_SUCCESS) {
                return $upload;
            }

            if ($oldPath) {
                $this->mediaStorage->deleteByPath($oldPath);
            }

            return [
                'message' => 'File is successfully saved',
                'payload' => [
                    'file' => $upload['payload']['path']
                ],
                'status' => CoreConstants::STATUS_CODE_SUCCESS
            ];
        } catch (\Throwable $th) {
            Log::error($th->getMessage());
            return [
                'message' => 'Something went wrong',
                'payload' => $th->getMessage(),
                'status'  => CoreConstants::STATUS_CODE_ERROR
            ];
        }
    }

    /**
     * Process the image file array
     *
     * @param array $fileArray
     * @param Project|null $project
     * @return array
     */
    private function processImages(Array $fileArray, $project = null)
    {
        try {
            $savedFileArray = [];
            $oldPaths = $project ? json_decode($project->images, true) : [];

            foreach ($fileArray as $key => $file) {
                try {
                    if ($file instanceof UploadedFile) {
                        $upload = $this->mediaStorage->upload($file, 'projects/images', [
                            'owner' => $project,
                            'collection' => 'images',
                        ]);
                        if ($upload['status'] === CoreConstants::STATUS_CODE_SUCCESS) {
                            array_push($savedFileArray, $upload['payload']['path']);
                        }
                    }
                } catch (\Throwable $th) {
                    Log::error($th->getMessage());
                }
            }

            if (count($savedFileArray)) {
                if (is_array($oldPaths)) {
                    foreach ($oldPaths as $oldPath) {
                        $this->mediaStorage->deleteByPath($oldPath);
                    }
                }

                return [
                    'message' => 'Files are successfully saved',
                    'payload' => [
                        'files' => $savedFileArray
                    ],
                    'status' => CoreConstants::STATUS_CODE_SUCCESS
                ];
            } else {
                return [
                    'message' => 'No file could not be saved',
                    'payload' => null,
                    'status' => CoreConstants::STATUS_CODE_ERROR
                ];
            }
        } catch (\Throwable $th) {
            Log::error($th->getMessage());
            return [
                'message' => 'Something went wrong',
                'payload' => $th->getMessage(),
                'status'  => CoreConstants::STATUS_CODE_ERROR
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
            } else {
                return [
                    'message' => 'No result is found',
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
     * Get all fields with paginate
     *
     * @param array $data
     * @param array $select
     * @return array
     */
    public function getAllWithPaginate(array $data, array $select = ['*'])
    {
        try {
            $perPage  = !empty($data['params']) && !empty(json_decode($data['params'])->pageSize) ? json_decode($data['params'])->pageSize : 10;
            
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
     * Delete items by id array
     *
     * @param array $ids
     * @return array
     */
    public function deleteByIds(array $ids)
    {
        try {
            $entries = $this->model->whereIn('id', $ids)->get();
            $deleted = 0;

            foreach ($entries as $key => $entry) {
                $this->mediaStorage->deleteByPath($entry->thumbnail);

                $existingImages = json_decode($entry->images, true);
                if (is_array($existingImages)) {
                    foreach ($existingImages as $existingImage) {
                        $this->mediaStorage->deleteByPath($existingImage);
                    }
                }
                $entry->delete();
                $deleted++;
            }

            if ($deleted) {
                return [
                    'message' => 'Data is deleted successfully',
                    'payload' => [
                        'totalDeleted' => $deleted
                    ],
                    'status'  => CoreConstants::STATUS_CODE_SUCCESS
                ];
            } else {
                return [
                    'message' => 'Nothing to Delete',
                    'payload' => null,
                    'status'  => CoreConstants::STATUS_CODE_NOT_FOUND
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
}
