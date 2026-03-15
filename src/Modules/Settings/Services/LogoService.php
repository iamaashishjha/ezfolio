<?php

declare(strict_types=1);

namespace Src\Modules\Settings\Services;

use App\Helpers\CoreConstants;
use Illuminate\Support\Facades\Log;
use Src\Modules\Settings\Adapters\SettingsDataAdapter;
use Src\Modules\Settings\Repositories\SettingRepositoryInterface;

final class LogoService
{
    public function __construct(
        private SettingRepositoryInterface $settingRepository,
        private SettingsDataAdapter $adapter,
        private \App\Services\MediaStorageService $mediaStorage
    ) {
    }

    public function update($file): array
    {
        try {
            $oldPath = $this->getCurrentSettingValue(CoreConstants::SETTING__LOGO);
            $upload = $this->mediaStorage->upload($file, 'settings/logo', array(
                'collection' => 'logo',
                'metadata' => array('setting_key' => CoreConstants::SETTING__LOGO),
            ));

            if (($upload['status'] ?? CoreConstants::STATUS_CODE_ERROR) !== CoreConstants::STATUS_CODE_SUCCESS) {
                return $upload;
            }

            $path = $upload['payload']['path'];
            $url = $upload['payload']['url'];

            $saved = $this->settingRepository->upsertByKey(CoreConstants::SETTING__LOGO, $path);
            $this->mediaStorage->attachToOwner($path, $saved, 'logo');
            $this->mediaStorage->deleteByPath($oldPath);

            return $this->adapter->toApiResponse('File is successfully saved', array('file' => $path, 'url' => $url), CoreConstants::STATUS_CODE_SUCCESS);
        } catch (\Throwable $throwable) {
            Log::error($throwable->getMessage());
            return $this->adapter->toApiResponse('Something went wrong', $throwable->getMessage(), CoreConstants::STATUS_CODE_ERROR);
        }
    }

    public function delete(): array
    {
        try {
            $oldPath = $this->getCurrentSettingValue(CoreConstants::SETTING__LOGO);
            $default = 'assets/common/img/logo/default.png';
            $this->settingRepository->upsertByKey(CoreConstants::SETTING__LOGO, $default);
            $this->mediaStorage->deleteByPath($oldPath);

            return $this->adapter->toApiResponse('Logo is deleted successfully', array('file' => $default, 'url' => $this->resolveMediaUrl($default)), CoreConstants::STATUS_CODE_SUCCESS);
        } catch (\Throwable $throwable) {
            Log::error($throwable->getMessage());
            return $this->adapter->toApiResponse('Something went wrong', $throwable->getMessage(), CoreConstants::STATUS_CODE_ERROR);
        }
    }

    private function getCurrentSettingValue(int $key): ?string
    {
        $setting = $this->settingRepository->getByKey($key, array('setting_value'));
        return $setting?->setting_value;
    }

    private function resolveMediaUrl(string $path): string
    {
        return $this->mediaStorage->resolveUrl(config('filesystems.media_disk', 'minio'), $path);
    }
}
