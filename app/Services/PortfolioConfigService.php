<?php

namespace App\Services;

use CoreConstants;
use App\Models\PortfolioConfig;
use App\Services\Contracts\PortfolioConfigInterface;
use Illuminate\Support\Str as SupportStr;
use Log;
use Validator;

class PortfolioConfigService implements PortfolioConfigInterface
{
    /**
     * Eloquent instance
     *
     * @var PortfolioConfig
     */
    private $model;

    /**
     * @var MediaStorageService
     */
    private $mediaStorage;

    /**
     * Create a new service instance.
     *
     * @return void
     */
    public function __construct(PortfolioConfig $portfolioConfig, MediaStorageService $mediaStorage)
    {
        $this->model = $portfolioConfig;
        $this->mediaStorage = $mediaStorage;
    }

    /**
     * If config exist, update it. Otherwise insert new
     *
     * @param array $data
     * @return array
     */
    public function insertOrUpdate(array $data)
    {
        try {
            $validate = Validator::make($data, [
                'setting_key' => 'required',
            ]);

            if ($validate->fails()) {
                return [
                    'message' => 'Validation Error',
                    'payload' => $validate->errors(),
                    'status'  => CoreConstants::STATUS_CODE_BAD_REQUEST
                ];
            }

            if (isset($data['default_value'])) {
                $result = $this->model->updateOrCreate([
                    'setting_key' => $data['setting_key'],
                ], [
                    'setting_value' => isset($data['setting_value']) ? $data['setting_value'] : '',
                    'default_value' => isset($data['default_value']) ? $data['default_value'] : ''
                ]);
            } else {
                $result = $this->model->updateOrCreate([
                    'setting_key' => $data['setting_key'],
                ], [
                    'setting_value' => $data['setting_value']
                ]);
            }

            if ($result) {
                return [
                    'message' => 'Data is successfully updated',
                    'payload' => $result,
                    'status'  => CoreConstants::STATUS_CODE_SUCCESS
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
                'status'  => CoreConstants::STATUS_CODE_ERROR
            ];
        }
    }

    /**
     * Get single config by key
     *
     * @param int $key
     * @param array $select
     * @return array
     */
    public function getConfigByKey(int $key, array $select = ['*'])
    {
        try {
            $result = $this->model
                        ->select($select)
                        ->where('setting_key', $key)
                        ->first();
            if ($result) {
                return [
                    'message' => 'Config is fetched successfully',
                    'payload' => $result,
                    'status' => CoreConstants::STATUS_CODE_SUCCESS
                ];
            } else {
                return [
                    'message' => 'Config is not found',
                    'payload' => null,
                    'status'  => CoreConstants::STATUS_CODE_NOT_FOUND
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
     * Get all related Configs
     *
     * @param boolean $accentColor
     * @param boolean $googleAnalyticsId
     * @param boolean $maintenanceMode
     * @param boolean $template
     * @param boolean $seo
     * @param boolean $visibility
     * @param boolean $script
     * @return array
     */
    public function getAllConfigData(
        bool $accentColor = true,
        bool $googleAnalyticsId = true,
        bool $maintenanceMode = true,
        bool $template = true,
        bool $seo = true,
        bool $visibility = true,
        bool $script = true
    ) {
        try {
            $data = [];

            if ($template) {
                $result = $this->getConfigByKey(CoreConstants::PORTFOLIO_CONFIG__TEMPLATE, ['setting_value']);
                if ($result['status'] === CoreConstants::STATUS_CODE_SUCCESS) {
                    $data['template'] = $result['payload']->setting_value;
                } else {
                    $data['template'] = 'procyon';
                }
            }

            if ($accentColor) {
                $result = $this->getConfigByKey(CoreConstants::PORTFOLIO_CONFIG__ACCENT_COLOR, ['setting_value']);
                if ($result['status'] === CoreConstants::STATUS_CODE_SUCCESS) {
                    $data['accentColor'] = $result['payload']->setting_value;
                } else {
                    $data['accentColor'] = '#1890ff';
                }
            }

            if ($googleAnalyticsId) {
                $result = $this->getConfigByKey(CoreConstants::PORTFOLIO_CONFIG__GOOGLE_ANALYTICS_ID, ['setting_value']);
                if ($result['status'] === CoreConstants::STATUS_CODE_SUCCESS) {
                    $data['googleAnalyticsId'] = $result['payload']->setting_value;
                } else {
                    $data['googleAnalyticsId'] = '';
                }
            }

            if ($maintenanceMode) {
                $result = $this->getConfigByKey(CoreConstants::PORTFOLIO_CONFIG__MAINTENANCE_MODE, ['setting_value']);
                if ($result['status'] === CoreConstants::STATUS_CODE_SUCCESS) {
                    $data['maintenanceMode'] = $result['payload']->setting_value;
                } else {
                    $data['maintenanceMode'] = CoreConstants::FALSE;
                }
            }

            if ($visibility) {
                $result = $this->getConfigByKey(CoreConstants::PORTFOLIO_CONFIG__VISIBILITY_ABOUT, ['setting_value']);
                if ($result['status'] === CoreConstants::STATUS_CODE_SUCCESS) {
                    $data['visibility']['about'] = $result['payload']->setting_value;
                } else {
                    $data['visibility']['about'] = CoreConstants::TRUE;
                }

                $result = $this->getConfigByKey(CoreConstants::PORTFOLIO_CONFIG__VISIBILITY_SKILL, ['setting_value']);
                if ($result['status'] === CoreConstants::STATUS_CODE_SUCCESS) {
                    $data['visibility']['skills'] = $result['payload']->setting_value;
                } else {
                    $data['visibility']['skills'] = CoreConstants::TRUE;
                }

                $result = $this->getConfigByKey(CoreConstants::PORTFOLIO_CONFIG__VISIBILITY_EDUCATION, ['setting_value']);
                if ($result['status'] === CoreConstants::STATUS_CODE_SUCCESS) {
                    $data['visibility']['education'] = $result['payload']->setting_value;
                } else {
                    $data['visibility']['education'] = CoreConstants::TRUE;
                }

                $result = $this->getConfigByKey(CoreConstants::PORTFOLIO_CONFIG__VISIBILITY_EXPERIENCE, ['setting_value']);
                if ($result['status'] === CoreConstants::STATUS_CODE_SUCCESS) {
                    $data['visibility']['experiences'] = $result['payload']->setting_value;
                } else {
                    $data['visibility']['experiences'] = CoreConstants::TRUE;
                }

                $result = $this->getConfigByKey(CoreConstants::PORTFOLIO_CONFIG__VISIBILITY_PROJECT, ['setting_value']);
                if ($result['status'] === CoreConstants::STATUS_CODE_SUCCESS) {
                    $data['visibility']['projects'] = $result['payload']->setting_value;
                } else {
                    $data['visibility']['projects'] = CoreConstants::TRUE;
                }

                $result = $this->getConfigByKey(CoreConstants::PORTFOLIO_CONFIG__VISIBILITY_SERVICE, ['setting_value']);
                if ($result['status'] === CoreConstants::STATUS_CODE_SUCCESS) {
                    $data['visibility']['services'] = $result['payload']->setting_value;
                } else {
                    $data['visibility']['services'] = CoreConstants::TRUE;
                }

                $result = $this->getConfigByKey(CoreConstants::PORTFOLIO_CONFIG__VISIBILITY_CONTACT, ['setting_value']);
                if ($result['status'] === CoreConstants::STATUS_CODE_SUCCESS) {
                    $data['visibility']['contact'] = $result['payload']->setting_value;
                } else {
                    $data['visibility']['contact'] = CoreConstants::TRUE;
                }

                $result = $this->getConfigByKey(CoreConstants::PORTFOLIO_CONFIG__VISIBILITY_FOOTER, ['setting_value']);
                if ($result['status'] === CoreConstants::STATUS_CODE_SUCCESS) {
                    $data['visibility']['footer'] = $result['payload']->setting_value;
                } else {
                    $data['visibility']['footer'] = CoreConstants::TRUE;
                }

                $result = $this->getConfigByKey(CoreConstants::PORTFOLIO_CONFIG__VISIBILITY_CV, ['setting_value']);
                if ($result['status'] === CoreConstants::STATUS_CODE_SUCCESS) {
                    $data['visibility']['cv'] = $result['payload']->setting_value;
                } else {
                    $data['visibility']['cv'] = CoreConstants::TRUE;
                }

                $result = $this->getConfigByKey(CoreConstants::PORTFOLIO_CONFIG__VISIBILITY_SKILL_PROFICIENCY, ['setting_value']);
                if ($result['status'] === CoreConstants::STATUS_CODE_SUCCESS) {
                    $data['visibility']['skillProficiency'] = $result['payload']->setting_value;
                } else {
                    $data['visibility']['skillProficiency'] = CoreConstants::TRUE;
                }

                $result = $this->getConfigByKey(CoreConstants::PORTFOLIO_CONFIG__VISIBILITY_BLOG, ['setting_value']);
                if ($result['status'] === CoreConstants::STATUS_CODE_SUCCESS) {
                    $data['visibility']['blog'] = $result['payload']->setting_value;
                } else {
                    $data['visibility']['blog'] = CoreConstants::TRUE;
                }
            }

            if ($script) {
                $result = $this->getConfigByKey(CoreConstants::PORTFOLIO_CONFIG__SCRIPT_HEADER, ['setting_value']);
                if ($result['status'] === CoreConstants::STATUS_CODE_SUCCESS) {
                    $data['script']['header'] = $result['payload']->setting_value;
                } else {
                    $data['script']['header'] = '';
                }

                $result = $this->getConfigByKey(CoreConstants::PORTFOLIO_CONFIG__SCRIPT_FOOTER, ['setting_value']);
                if ($result['status'] === CoreConstants::STATUS_CODE_SUCCESS) {
                    $data['script']['footer'] = $result['payload']->setting_value;
                } else {
                    $data['script']['footer'] = '';
                }
            }

            if ($seo) {
                $result = $this->getConfigByKey(CoreConstants::PORTFOLIO_CONFIG__META_TITLE, ['setting_value']);
                if ($result['status'] === CoreConstants::STATUS_CODE_SUCCESS) {
                    $data['seo']['title'] = $result['payload']->setting_value;
                } else {
                    $data['seo']['title'] = '';
                }

                $result = $this->getConfigByKey(CoreConstants::PORTFOLIO_CONFIG__META_AUTHOR, ['setting_value']);
                if ($result['status'] === CoreConstants::STATUS_CODE_SUCCESS) {
                    $data['seo']['author'] = $result['payload']->setting_value;
                } else {
                    $data['seo']['author'] = '';
                }

                $result = $this->getConfigByKey(CoreConstants::PORTFOLIO_CONFIG__META_DESCRIPTION, ['setting_value']);
                if ($result['status'] === CoreConstants::STATUS_CODE_SUCCESS) {
                    $data['seo']['description'] = $result['payload']->setting_value;
                } else {
                    $data['seo']['description'] = '';
                }

                $result = $this->getConfigByKey(CoreConstants::PORTFOLIO_CONFIG__META_IMAGE, ['setting_value']);
                if ($result['status'] === CoreConstants::STATUS_CODE_SUCCESS) {
                    $data['seo']['image'] = $result['payload']->setting_value;
                } else {
                    $data['seo']['image'] = '';
                }
                $data['seo']['image_url'] = $this->resolveMediaUrl($data['seo']['image']);
            }

            return [
                'message' => 'Configs are fetched successfully',
                'payload' => $data,
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
     * Set single Config
     *
     * @param array $data
     * @return array
     */
    public function setConfigData(array $data)
    {
        try {
            $validate = Validator::make($data, [
                'setting_key' => 'required',
            ]);

            if ($validate->fails()) {
                return [
                    'message' => 'Validation Error',
                    'payload' => $validate->errors(),
                    'status' => CoreConstants::STATUS_CODE_BAD_REQUEST
                ];
            }

            $newData['setting_key']   = $data['setting_key'];
            $newData['setting_value'] = isset($data['setting_value']) ? $data['setting_value'] : '';

            $result = $this->insertOrUpdate($newData);
            
            if ($result['status'] === CoreConstants::STATUS_CODE_SUCCESS) {
                return [
                    'message' => 'Config is successfully updated',
                    'payload' => $result['payload'],
                    'status' => CoreConstants::STATUS_CODE_SUCCESS
                ];
            } else {
                return $result;
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
     * Store meta data
     *
     * @param array $data
     * @return array
     */
    public function setMetaData(array $data)
    {
        try {
            $count = 0;
            $inserted = [];

            foreach ($data as $key => $value) {
                if ($key === 'title') {
                    $newData = [
                        'setting_key' => CoreConstants::PORTFOLIO_CONFIG__META_TITLE,
                        'setting_value' => isset($value) ? $value : '',
                    ];
                    $result = $this->insertOrUpdate($newData);

                    if ($result['status'] === CoreConstants::STATUS_CODE_SUCCESS) {
                        $count++;
                        $inserted['title'] = $result['payload']->setting_value;
                    } else {
                        Log::error($result['payload']);
                    }
                } elseif ($key === 'author') {
                    $newData = [
                        'setting_key' => CoreConstants::PORTFOLIO_CONFIG__META_AUTHOR,
                        'setting_value' => isset($value) ? $value : '',
                    ];
                    $result = $this->insertOrUpdate($newData);

                    if ($result['status'] === CoreConstants::STATUS_CODE_SUCCESS) {
                        $count++;
                        $inserted['author'] = $result['payload']->setting_value;
                    } else {
                        Log::error($result['payload']);
                    }
                } elseif ($key === 'description') {
                    $newData = [
                        'setting_key' => CoreConstants::PORTFOLIO_CONFIG__META_DESCRIPTION,
                        'setting_value' => isset($value) ? $value : '',
                    ];
                    $result = $this->insertOrUpdate($newData);

                    if ($result['status'] === CoreConstants::STATUS_CODE_SUCCESS) {
                        $count++;
                        $inserted['description'] = $result['payload']->setting_value;
                    } else {
                        Log::error($result['payload']);
                    }
                } elseif ($key === 'image') {
                    $file = $data['image'];
                    if ($file) {
                        $oldImagePath = $this->getCurrentConfigValue(CoreConstants::PORTFOLIO_CONFIG__META_IMAGE);
                        $upload = $this->mediaStorage->upload($file, 'portfolio/meta-image', [
                            'collection' => 'seo_image',
                            'metadata' => ['setting_key' => CoreConstants::PORTFOLIO_CONFIG__META_IMAGE],
                        ]);

                        if ($upload['status'] === CoreConstants::STATUS_CODE_SUCCESS) {
                            $path = $upload['payload']['path'];
                            $url = $upload['payload']['url'];
                            $newData = [
                                'setting_key' => CoreConstants::PORTFOLIO_CONFIG__META_IMAGE,
                                'setting_value' => $path,
                            ];
                            $result = $this->insertOrUpdate($newData);

                            if ($result['status'] === CoreConstants::STATUS_CODE_SUCCESS) {
                                $count++;
                                $inserted['image'] = $path;
                                $inserted['image_url'] = $url;
                                $this->mediaStorage->attachToOwner($path, $result['payload'], 'seo_image');
                                $this->mediaStorage->deleteByPath($oldImagePath);
                            } else {
                                $this->mediaStorage->deleteByPath($path);
                                Log::error($result['payload']);
                            }
                        }
                    } else {
                        $oldImagePath = $this->getCurrentConfigValue(CoreConstants::PORTFOLIO_CONFIG__META_IMAGE);
                        $this->mediaStorage->deleteByPath($oldImagePath);

                        $newData = [
                            'setting_key' => CoreConstants::PORTFOLIO_CONFIG__META_IMAGE,
                            'setting_value' => '',
                        ];
                        $result = $this->insertOrUpdate($newData);
                        if ($result['status'] === CoreConstants::STATUS_CODE_SUCCESS) {
                            $inserted['image'] = '';
                            $inserted['image_url'] = '';
                        }
                    }
                }
            }
            if ($count) {
                return [
                    'message' => 'SEO info is successfully saved',
                    'payload' => [
                        'count' => $count,
                        'inserted' => $inserted
                    ],
                    'status' => CoreConstants::STATUS_CODE_SUCCESS
                ];
            } else {
                return [
                    'message' => 'Nothing is updated',
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
     * Resolve media URL from a stored path/key.
     *
     * @param string|null $path
     * @return string
     */
    private function resolveMediaUrl(?string $path)
    {
        if (!$path) {
            return '';
        }

        if (SupportStr::startsWith($path, 'http://') || SupportStr::startsWith($path, 'https://')) {
            return $path;
        }

        return $this->mediaStorage->resolveUrl(config('filesystems.media_disk', 'minio'), $path);
    }

    /**
     * Get stored config value by key.
     *
     * @param int $key
     * @return string|null
     */
    private function getCurrentConfigValue(int $key)
    {
        $result = $this->getConfigByKey($key, ['setting_value']);
        if ($result['status'] === CoreConstants::STATUS_CODE_SUCCESS) {
            return $result['payload']->setting_value;
        }

        return null;
    }
}
