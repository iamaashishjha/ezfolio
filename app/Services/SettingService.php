<?php

namespace App\Services;

use CoreConstants;
use App\Models\Setting;
use App\Services\Contracts\AboutInterface;
use App\Services\Contracts\SettingInterface;
use Config;
use Illuminate\Support\Str as SupportStr;
use Log;
use Jackiedo\DotenvEditor\Facades\DotenvEditor;
use Validator;

class SettingService implements SettingInterface
{
    /**
     * Eloquent instance
     *
     * @var Setting
     */
    private $model;

    /**
     * @var MediaStorageService
     */
    private $mediaStorage;

    /**
     * Create a new service instance.
     *
     * @param Setting $setting
     * @return void
     */
    public function __construct(Setting $setting, MediaStorageService $mediaStorage)
    {
        $this->model = $setting;
        $this->mediaStorage = $mediaStorage;
    }

    /**
     * If setting exist, update it. Otherwise insert new
     *
     * @param array $data
     * @return array
     */
    public function insertOrUpdate(array $data)
    {
        try {
            $validate = Validator::make($data, [
                'setting_key' => 'required',
                'setting_value' => 'required',
                'default_value' => 'required|sometimes',
            ]);

            if ($validate->fails()) {
                return [
                    'message' => 'Bad Request',
                    'payload' => $validate->errors(),
                    'status' => CoreConstants::STATUS_CODE_BAD_REQUEST
                ];
            }

            if (isset($data['default_value'])) {
                $result = $this->model->updateOrCreate([
                    'setting_key' => $data['setting_key'],
                ], [
                    'setting_value' => $data['setting_value'],
                    'default_value' => $data['default_value']
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
                    'message' => 'Data is saved successfully',
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
     * Get single setting by key
     *
     * @param int $key
     * @param array $select
     * @return array
     */
    public function getSettingByKey(int $key, array $select = ['*'])
    {
        try {
            $result = $this->model
                        ->select($select)
                        ->where('setting_key', $key)
                        ->first();

            if ($result) {
                return [
                    'message' => 'Setting is fetched successfully',
                    'payload' => $result,
                    'status' => CoreConstants::STATUS_CODE_SUCCESS
                ];
            } else {
                return [
                    'message' => 'Setting is not found',
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
     * Get all related settings
     *
     * @return array
     */
    public function getSettingsData()
    {
        try {
            //get accent color
            $result = $this->getSettingByKey(CoreConstants::SETTING__ACCENT_COLOR, ['setting_value']);

            if ($result['status'] === CoreConstants::STATUS_CODE_SUCCESS) {
                $data['accentColor'] = $result['payload']->setting_value;
            } else {
                $data['accentColor'] = '#1890ff';
            }

            //get short menu
            $result = $this->getSettingByKey(CoreConstants::SETTING__SHORT_MENU, ['setting_value']);

            if ($result['status'] === CoreConstants::STATUS_CODE_SUCCESS) {
                $data['shortMenu'] = ($result['payload']->setting_value === true || $result['payload']->setting_value === 'true' || $result['payload']->setting_value === 1 || $result['payload']->setting_value === '1' ? true : false);
            } else {
                $data['shortMenu'] = false;
            }

            //get menu layout
            $result = $this->getSettingByKey(CoreConstants::SETTING__MENU_LAYOUT, ['setting_value']);

            if ($result['status'] === CoreConstants::STATUS_CODE_SUCCESS) {
                $data['menuLayout'] = $result['payload']->setting_value;
            } else {
                $data['menuLayout'] = 'mix';
            }

            //get menu color
            $result = $this->getSettingByKey(CoreConstants::SETTING__MENU_COLOR, ['setting_value']);

            if ($result['status'] === CoreConstants::STATUS_CODE_SUCCESS) {
                $data['menuColor'] = $result['payload']->setting_value;
            } else {
                $data['menuColor'] = 'light';
            }

            //get nav color
            $result = $this->getSettingByKey(CoreConstants::SETTING__NAV_COLOR, ['setting_value']);

            if ($result['status'] === CoreConstants::STATUS_CODE_SUCCESS) {
                $data['navColor'] = $result['payload']->setting_value;
            } else {
                $data['navColor'] = 'light';
            }

            //get site name
            $data['siteName'] = DotenvEditor::getValue('SITE_NAME') ?: Config::get('custom.site_name');

            //get logo
            $result = $this->getSettingByKey(CoreConstants::SETTING__LOGO, ['setting_value']);

            if ($result['status'] === CoreConstants::STATUS_CODE_SUCCESS) {
                $data['logo'] = $result['payload']->setting_value;
            } else {
                $data['logo'] = 'assets/common/img/logo/default.png';
            }
            $data['logo_url'] = $this->resolveMediaUrl($data['logo']);

            //get favicon
            $result = $this->getSettingByKey(CoreConstants::SETTING__FAVICON, ['setting_value']);

            if ($result['status'] === CoreConstants::STATUS_CODE_SUCCESS) {
                $data['favicon'] = $result['payload']->setting_value;
            } else {
                $data['favicon'] = 'assets/common/img/favicon/default.png';
            }
            $data['favicon_url'] = $this->resolveMediaUrl($data['favicon']);

            //get cover photo
            $about = resolve(AboutInterface::class);

            $result = $about->getAll(['cover', 'id']);

            if ($result['status'] === CoreConstants::STATUS_CODE_SUCCESS) {
                $data['cover'] = $result['payload']->cover;
                $data['cover_url'] = $result['payload']->cover_url;
            } else {
                $data['cover'] = 'assets/common/img/cover/default.png';
                $data['cover_url'] = asset('assets/common/img/cover/default.png');
            }

            //get avatar
            $result = $about->getAll(['avatar', 'id']);

            if ($result['status'] === CoreConstants::STATUS_CODE_SUCCESS) {
                $data['avatar'] = $result['payload']->avatar;
                $data['avatar_url'] = $result['payload']->avatar_url;
            } else {
                $data['avatar'] = 'assets/common/img/avatar/default.png';
                $data['avatar_url'] = asset('assets/common/img/avatar/default.png');
            }

            //get mail setting
            $data['mailSettings']['MAIL_MAILER'] = env('MAIL_MAILER');
            $data['mailSettings']['MAIL_HOST'] = env('MAIL_HOST');
            $data['mailSettings']['MAIL_PORT'] = env('MAIL_PORT');
            $data['mailSettings']['MAIL_USERNAME'] = env('MAIL_USERNAME');
            $data['mailSettings']['MAIL_PASSWORD'] = env('MAIL_PASSWORD');
            $data['mailSettings']['MAIL_ENCRYPTION'] = env('MAIL_ENCRYPTION');
            $data['mailSettings']['MAIL_FROM_ADDRESS'] = env('MAIL_FROM_ADDRESS');
            $data['mailSettings']['MAIL_FROM_NAME'] = env('MAIL_FROM_NAME');

            //get demo mode
            $data['demoMode'] = Config::get('custom.demo_mode');
            
            return [
                'message' => 'Settings are fetched successfully',
                'payload' => $data,
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
     * Set single setting
     *
     * @param array $data
     * @return array
     */
    public function setSettingData(array $data)
    {
        try {
            $validate = Validator::make($data, [
                'setting_key' => 'required',
                'setting_value' => 'required'
            ]);

            if ($validate->fails()) {
                return [
                    'message' => 'Bad Request',
                    'payload' => $validate->errors(),
                    'status' => CoreConstants::STATUS_CODE_BAD_REQUEST
                ];
            }
            
            if ($data['setting_key'] === CoreConstants::SETTING__SITE_NAME) {
                $result = $this->updateSiteName($data['setting_value']);
            } else {
                $newData['setting_key'] = $data['setting_key'];
                $newData['setting_value'] = $data['setting_value'];

                $result = $this->insertOrUpdate($newData);
            }
            
            return $result;
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
     * Update the UI site name environment variable.
     *
     * @param string $newName
     * @return array
     */
    public function updateSiteName(string $newName)
    {
        try {
            $file = DotenvEditor::setKey('SITE_NAME', $newName);
            $file = DotenvEditor::save();
            if ($file) {
                return [
                    'message' => 'Site name is successfully updated',
                    'payload' => $newName,
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
     * Process the update logo request
     *
     * @param array $data
     * @return array
     */
    public function processUpdateLogoRequest(array $data)
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
            $file = $data['file'];
            $oldPath = $this->getCurrentSettingValue(CoreConstants::SETTING__LOGO);
            $uploadResult = $this->mediaStorage->upload($file, 'settings/logo', [
                'collection' => 'logo',
                'metadata' => ['setting_key' => CoreConstants::SETTING__LOGO],
            ]);

            if ($uploadResult['status'] !== CoreConstants::STATUS_CODE_SUCCESS) {
                return $uploadResult;
            }

            $path = $uploadResult['payload']['path'];
            $url = $uploadResult['payload']['url'];

            $result  = $this->setSettingData([
                'setting_key'  => CoreConstants::SETTING__LOGO,
                'setting_value' => $path,
            ]);

            if ($result['status'] !== CoreConstants::STATUS_CODE_SUCCESS) {
                $this->mediaStorage->deleteByPath($path);
                return $result;
            }

            $this->mediaStorage->attachToOwner($path, $result['payload'], 'logo');
            $this->mediaStorage->deleteByPath($oldPath);

            return [
                'message' => 'File is successfully saved',
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
     * Process the delete logo request
     *
     * @param string $file
     * @return array
     */
    public function processDeleteLogoRequest(string $file)
    {
        try {
            $oldPath = $this->getCurrentSettingValue(CoreConstants::SETTING__LOGO);
            $defaultLogo = 'assets/common/img/logo/default.png';

            $result  = $this->setSettingData([
                'setting_key' => CoreConstants::SETTING__LOGO,
                'setting_value' => $defaultLogo,
            ]);

            if ($result['status'] !== CoreConstants::STATUS_CODE_SUCCESS) {
                return $result;
            }

            $this->mediaStorage->deleteByPath($oldPath);

            return [
                'message' => 'Logo is deleted successfully',
                'payload' => [
                    'file' => $defaultLogo,
                    'url' => $this->resolveMediaUrl($defaultLogo),
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
     * Process the update favicon request
     *
     * @param array $data
     * @return array
     */
    public function processUpdateFaviconRequest(array $data)
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

            $file = $data['file'];
            $oldPath = $this->getCurrentSettingValue(CoreConstants::SETTING__FAVICON);
            $uploadResult = $this->mediaStorage->upload($file, 'settings/favicon', [
                'collection' => 'favicon',
                'metadata' => ['setting_key' => CoreConstants::SETTING__FAVICON],
            ]);

            if ($uploadResult['status'] !== CoreConstants::STATUS_CODE_SUCCESS) {
                return $uploadResult;
            }

            $path = $uploadResult['payload']['path'];
            $url = $uploadResult['payload']['url'];

            $result  = $this->setSettingData([
                'setting_key' => CoreConstants::SETTING__FAVICON,
                'setting_value' => $path,
            ]);

            if ($result['status'] !== CoreConstants::STATUS_CODE_SUCCESS) {
                $this->mediaStorage->deleteByPath($path);
                return $result;
            }

            $this->mediaStorage->attachToOwner($path, $result['payload'], 'favicon');
            $this->mediaStorage->deleteByPath($oldPath);

            return [
                'message' => 'Favicon is successfully saved',
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
     * Process the delete favicon request
     *
     * @param string $file
     * @return array
     */
    public function processDeleteFaviconRequest(string $file)
    {
        try {
            $oldPath = $this->getCurrentSettingValue(CoreConstants::SETTING__FAVICON);
            $defaultFavicon = 'assets/common/img/favicon/default.png';
            $result  = $this->setSettingData([
                'setting_key' => CoreConstants::SETTING__FAVICON,
                'setting_value' => $defaultFavicon,
            ]);

            if ($result['status'] !== CoreConstants::STATUS_CODE_SUCCESS) {
                return $result;
            }

            $this->mediaStorage->deleteByPath($oldPath);

            return [
                'message' => 'File is deleted successfully',
                'payload' => [
                    'file' => $defaultFavicon,
                    'url' => $this->resolveMediaUrl($defaultFavicon),
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
     * store mail settings
     *
     * @param array $data
     * @return array
     */
    public function storeMailSettings(array $data)
    {
        try {
            $validate = Validator::make($data, [
                'MAIL_MAILER' => 'required',
                'MAIL_HOST' => 'required',
                'MAIL_PORT' => 'required',
                'MAIL_USERNAME' => 'required',
                'MAIL_PASSWORD' => 'required',
                'MAIL_ENCRYPTION' => 'required',
            ]);

            if ($validate->fails()) {
                return [
                    'message' => 'Bad Request',
                    'payload' => $validate->errors(),
                    'status' => CoreConstants::STATUS_CODE_BAD_REQUEST
                ];
            }
            
            $file = DotenvEditor::setKey('MAIL_MAILER', $data['MAIL_MAILER']);
            $file = DotenvEditor::setKey('MAIL_HOST', $data['MAIL_HOST']);
            $file = DotenvEditor::setKey('MAIL_PORT', $data['MAIL_PORT']);
            $file = DotenvEditor::setKey('MAIL_USERNAME', $data['MAIL_USERNAME']);
            $file = DotenvEditor::setKey('MAIL_PASSWORD', $data['MAIL_PASSWORD']);
            $file = DotenvEditor::setKey('MAIL_ENCRYPTION', $data['MAIL_ENCRYPTION']);
            $file = DotenvEditor::setKey('MAIL_FROM_ADDRESS', !empty($data['MAIL_FROM_ADDRESS']) ? $data['MAIL_FROM_ADDRESS'] : '');
            $file = DotenvEditor::setKey('MAIL_FROM_NAME', !empty($data['MAIL_FROM_NAME']) ? $data['MAIL_FROM_NAME'] : '');
            $file = DotenvEditor::save();

            if ($file) {
                return [
                    'message' => 'Mail setting is successfully updated',
                    'payload' => null,
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
     * Resolve media URL from a stored path/key.
     *
     * @param string|null $path
     * @return string|null
     */
    private function resolveMediaUrl(?string $path)
    {
        if (!$path) {
            return null;
        }

        if (SupportStr::startsWith($path, 'http://') || SupportStr::startsWith($path, 'https://')) {
            return $path;
        }

        return $this->mediaStorage->resolveUrl(config('filesystems.media_disk', 'minio'), $path);
    }

    /**
     * Get current value for the given setting key.
     *
     * @param int $key
     * @return string|null
     */
    private function getCurrentSettingValue(int $key)
    {
        $result = $this->getSettingByKey($key, ['setting_value']);
        if ($result['status'] === CoreConstants::STATUS_CODE_SUCCESS) {
            return $result['payload']->setting_value;
        }

        return null;
    }
}
