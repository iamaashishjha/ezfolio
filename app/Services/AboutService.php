<?php

namespace App\Services;

use CoreConstants;
use App\Models\About;
use App\Services\Contracts\AboutInterface;
use Log;
use Validator;

class AboutService implements AboutInterface
{
    /**
     * Eloquent instance
     *
     * @var About
     */
    private $model;

    /**
     * @var MediaStorageService
     */
    private $mediaStorage;

    /**
     * Create a new service instance
     *
     * @param About $about
     * @return void
     */
    public function __construct(About $about, MediaStorageService $mediaStorage)
    {
        $this->model = $about;
        $this->mediaStorage = $mediaStorage;
    }

    /**
     * Get all about fields
     *
     * @param array $select
     * @return array
     */
    public function getAll(array $select = ['*'])
    {
        try {
            $result = $this->model->select($select)->first();

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
                'email' => 'required|email'
            ]);

            if ($validate->fails()) {
                return [
                    'message' => 'Bad Request',
                    'payload' => $validate->errors(),
                    'status' => CoreConstants::STATUS_CODE_BAD_REQUEST
                ];
            }

            $newData['name'] = $data['name'];
            $newData['email'] = $data['email'];
            $newData['job_title'] = isset($data['job_title']) ? $data['job_title'] : null;
            $newData['phone'] = isset($data['phone']) ? $data['phone'] : null;
            $newData['address'] = isset($data['address']) ? $data['address'] : null;
            $newData['description'] = isset($data['description']) ? $data['description'] : null;

            if (isset($data['seederCV'])) {
                $newData['cv'] = $data['seederCV'];
            }
            
            $newTagLinesArray = [];
            if (isset($data['taglines'])) {
                foreach ($data['taglines'] as $key => $tagline) {
                    if ($tagline !== null && $tagline !== '') {
                        array_push($newTagLinesArray, $tagline);
                    }
                }
            }
            $newData['taglines'] = count($newTagLinesArray) ? json_encode($newTagLinesArray) : null;
            $newData['hero_subtitle'] = isset($data['hero_subtitle']) ? $data['hero_subtitle'] : null;

            $newHighlightsArray = [];
            if (isset($data['about_highlights'])) {
                foreach ($data['about_highlights'] as $key => $highlight) {
                    if ($highlight !== null && $highlight !== '') {
                        array_push($newHighlightsArray, $highlight);
                    }
                }
            }
            $newData['about_highlights'] = count($newHighlightsArray) ? json_encode($newHighlightsArray) : null;
            
            $newSocialLinksArray = [];
            if (isset($data['social_links'])) {
                foreach ($data['social_links'] as $key => $socialLink) {
                    if ($socialLink !== '' && !empty($socialLink['title']) && !empty($socialLink['link']) && !empty($socialLink['iconClass'])) {
                        array_push($newSocialLinksArray, $socialLink);
                    }
                }
            }
            $newData['social_links'] = count($newSocialLinksArray) ? json_encode($newSocialLinksArray) : null;
            
            $existedRecord = $this->getAll();

            if ($existedRecord['status'] === CoreConstants::STATUS_CODE_SUCCESS) {
                $existedRecord = $existedRecord['payload'];
                $result = $existedRecord->update($newData);
            } else {
                $newData['avatar'] = 'assets/common/img/avatar/default.png';
                $newData['cover'] = 'assets/common/img/cover/default.png';
                $result = $this->model->create($newData);
            }

            if ($result) {
                return [
                    'message' => 'Data is successfully updated',
                    'payload' => $result,
                    'status' => CoreConstants::STATUS_CODE_SUCCESS
                ];
            } else {
                return [
                    'message' => 'Something went wrong',
                    'payload' => null,
                    'status'  => CoreConstants::STATUS_CODE_ERROR
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
     * Process the update avatar request
     *
     * @param array $data
     * @return array
     */
    public function processUpdateAvatarRequest(array $data)
    {
        try {
            $validate = Validator::make($data, [
                'file' => 'required'
            ]);

            if ($validate->fails()) {
                return [
                    'message' => 'Bad Request',
                    'payload' => $validate->errors(),
                    'status' => CoreConstants::STATUS_CODE_BAD_REQUEST
                ];
            }

            $recordResult = $this->getAll(['id', 'avatar']);
            if ($recordResult['status'] !== CoreConstants::STATUS_CODE_SUCCESS) {
                return $recordResult;
            }

            $about = $recordResult['payload'];
            $oldPath = $about->avatar;

            $upload = $this->mediaStorage->upload($data['file'], 'about/avatar', [
                'owner' => $about,
                'collection' => 'avatar',
            ]);

            if ($upload['status'] !== CoreConstants::STATUS_CODE_SUCCESS) {
                return [
                    'message' => 'File could not be saved',
                    'payload' => null,
                    'status' => CoreConstants::STATUS_CODE_ERROR
                ];
            }

            $path = $upload['payload']['path'];
            $url = $upload['payload']['url'];

            $updateResponse = $about->update(['avatar' => $path]);
            if (!$updateResponse) {
                $this->mediaStorage->deleteByPath($path);
                return [
                    'message' => 'Something went wrong',
                    'payload' => null,
                    'status' => CoreConstants::STATUS_CODE_ERROR
                ];
            }

            $this->mediaStorage->attachToOwner($path, $about, 'avatar');
            $this->mediaStorage->deleteByPath($oldPath);

            return [
                'message' => 'Avatar is successfully saved',
                'payload' => [
                    'file' => $path,
                    'url' => $url,
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

    /**
     * Process the delete avatar request
     *
     * @param string $file
     * @return array
     */
    public function processDeleteAvatarRequest(string $file)
    {
        try {
            $result = $this->getAll(['id', 'avatar']);
            if ($result['status'] !== CoreConstants::STATUS_CODE_SUCCESS) {
                return $result;
            }

            $about = $result['payload'];
            $oldPath = $about->avatar ?: $file;
            $defaultAvatar = 'assets/common/img/avatar/default.png';

            $updateResponse = $about->update(['avatar' => $defaultAvatar]);
            if (!$updateResponse) {
                return [
                    'message' => 'Something went wrong',
                    'payload' => null,
                    'status' => CoreConstants::STATUS_CODE_ERROR
                ];
            }

            $this->mediaStorage->deleteByPath($oldPath);

            return [
                'message' => 'File is deleted successfully',
                'payload' => [
                    'file' => $defaultAvatar,
                    'url' => asset($defaultAvatar),
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

    /**
     * Process the update cover request
     *
     * @param array $data
     * @return array
     */
    public function processUpdateCoverRequest(array $data)
    {
        try {
            $validate = Validator::make($data, [
                'file' => 'required'
            ]);

            if ($validate->fails()) {
                return [
                    'message' => 'Bad Request',
                    'payload' => $validate->errors(),
                    'status' => CoreConstants::STATUS_CODE_BAD_REQUEST
                ];
            }

            $recordResult = $this->getAll(['id', 'cover']);
            if ($recordResult['status'] !== CoreConstants::STATUS_CODE_SUCCESS) {
                return $recordResult;
            }

            $about = $recordResult['payload'];
            $oldPath = $about->cover;

            $upload = $this->mediaStorage->upload($data['file'], 'about/cover', [
                'owner' => $about,
                'collection' => 'cover',
            ]);
            if ($upload['status'] !== CoreConstants::STATUS_CODE_SUCCESS) {
                return [
                    'message' => 'File could not be saved',
                    'payload' => null,
                    'status' => CoreConstants::STATUS_CODE_ERROR
                ];
            }

            $path = $upload['payload']['path'];
            $url = $upload['payload']['url'];

            $updateResponse = $about->update(['cover' => $path]);
            if (!$updateResponse) {
                $this->mediaStorage->deleteByPath($path);
                return [
                    'message' => 'Something went wrong',
                    'payload' => null,
                    'status' => CoreConstants::STATUS_CODE_ERROR
                ];
            }

            $this->mediaStorage->attachToOwner($path, $about, 'cover');
            $this->mediaStorage->deleteByPath($oldPath);

            return [
                'message' => 'cover is successfully saved',
                'payload' => [
                    'file' => $path,
                    'url' => $url,
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

    /**
     * Process the delete cover request
     *
     * @param string $file
     * @return array
     */
    public function processDeleteCoverRequest(string $file)
    {
        try {
            $result = $this->getAll(['id', 'cover']);
            if ($result['status'] !== CoreConstants::STATUS_CODE_SUCCESS) {
                return $result;
            }

            $about = $result['payload'];
            $oldPath = $about->cover ?: $file;
            $defaultCover = 'assets/common/img/cover/default.png';

            $updateResponse = $about->update(['cover' => $defaultCover]);
            if (!$updateResponse) {
                return [
                    'message' => 'Something went wrong',
                    'payload' => null,
                    'status' => CoreConstants::STATUS_CODE_ERROR
                ];
            }

            $this->mediaStorage->deleteByPath($oldPath);

            return [
                'message' => 'File is deleted successfully',
                'payload' => [
                    'file' => $defaultCover,
                    'url' => asset($defaultCover),
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

    /**
     * Process the update CV request
     *
     * @param array $data
     * @return array
     */
    public function processUpdateCVRequest(array $data)
    {
        try {
            $validate = Validator::make($data, [
                'file' => 'required'
            ]);

            if ($validate->fails()) {
                return [
                    'message' => 'Bad Request',
                    'payload' => $validate->errors(),
                    'status' => CoreConstants::STATUS_CODE_BAD_REQUEST
                ];
            }
          
            $recordResult = $this->getAll(['id', 'cv']);
            if ($recordResult['status'] !== CoreConstants::STATUS_CODE_SUCCESS) {
                return $recordResult;
            }

            $about = $recordResult['payload'];
            $oldPath = $about->cv;

            $upload = $this->mediaStorage->upload($data['file'], 'about/cv', [
                'owner' => $about,
                'collection' => 'cv',
                'visibility' => 'private',
            ]);
            if ($upload['status'] !== CoreConstants::STATUS_CODE_SUCCESS) {
                return [
                    'message' => 'File could not be saved',
                    'payload' => null,
                    'status' => CoreConstants::STATUS_CODE_ERROR
                ];
            }

            $path = $upload['payload']['path'];
            $url = $upload['payload']['url'];

            $updateResponse = $about->update(['cv' => $path]);
            if (!$updateResponse) {
                $this->mediaStorage->deleteByPath($path);
                return [
                    'message' => 'Something went wrong',
                    'payload' => null,
                    'status' => CoreConstants::STATUS_CODE_ERROR
                ];
            }

            $this->mediaStorage->attachToOwner($path, $about, 'cv');
            $this->mediaStorage->deleteByPath($oldPath);

            return [
                'message' => 'CV is successfully saved',
                'payload' => [
                    'file' => $path,
                    'url' => $url,
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

    /**
     * Process the delete CV request
     *
     * @param string $file
     * @return array
     */
    public function processDeleteCVRequest(string $file)
    {
        try {
            $result = $this->getAll(['id', 'cv']);
            if ($result['status'] !== CoreConstants::STATUS_CODE_SUCCESS) {
                return $result;
            }

            $about = $result['payload'];
            $oldPath = $about->cv ?: $file;

            $updateResponse = $about->update(['cv' => null]);
            if (!$updateResponse) {
                return [
                    'message'  => 'Something went wrong',
                    'payload' => null,
                    'status'  => CoreConstants::STATUS_CODE_ERROR
                ];
            }

            $this->mediaStorage->deleteByPath($oldPath);

            return [
                'message' => 'File is deleted successfully',
                'payload' => [
                    'file' => null,
                    'url' => null,
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
}
