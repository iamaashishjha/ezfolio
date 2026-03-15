<?php

declare(strict_types=1);

namespace Src\Modules\Settings\Services;

use App\Helpers\CoreConstants;
use App\Support\EnvEditor;
use Illuminate\Support\Facades\Log;
use Src\Modules\About\Repositories\AboutRepositoryInterface;
use Src\Modules\Settings\Adapters\SettingsDataAdapter;
use Src\Modules\Settings\Repositories\SettingRepositoryInterface;

final class GetSettingsService
{
    public function __construct(
        private SettingRepositoryInterface $settingRepository,
        private AboutRepositoryInterface $aboutRepository,
        private SettingsDataAdapter $adapter,
        private \App\Services\MediaStorageService $mediaStorage
    ) {
    }

    public function handle(): array
    {
        try {
            $data = array();
            $data['accentColor'] = $this->getSettingValue(CoreConstants::SETTING__ACCENT_COLOR, '#1890ff');
            $data['shortMenu'] = $this->toBool($this->getSettingValue(CoreConstants::SETTING__SHORT_MENU, false));
            $data['menuLayout'] = $this->getSettingValue(CoreConstants::SETTING__MENU_LAYOUT, 'mix');
            $data['menuColor'] = $this->getSettingValue(CoreConstants::SETTING__MENU_COLOR, 'light');
            $data['navColor'] = $this->getSettingValue(CoreConstants::SETTING__NAV_COLOR, 'light');
            $data['siteName'] = EnvEditor::get('APP_NAME');

            $data['logo'] = $this->getSettingValue(CoreConstants::SETTING__LOGO, 'assets/common/img/logo/default.png');
            $data['logo_url'] = $this->resolveMediaUrl($data['logo']);

            $data['favicon'] = $this->getSettingValue(CoreConstants::SETTING__FAVICON, 'assets/common/img/favicon/default.png');
            $data['favicon_url'] = $this->resolveMediaUrl($data['favicon']);

            $about = $this->aboutRepository->first();
            if ($about !== null) {
                $data['cover'] = $about->cover;
                $data['cover_url'] = $about->cover_url;
                $data['avatar'] = $about->avatar;
                $data['avatar_url'] = $about->avatar_url;
            } else {
                $data['cover'] = 'assets/common/img/cover/default.png';
                $data['cover_url'] = asset('assets/common/img/cover/default.png');
                $data['avatar'] = 'assets/common/img/avatar/default.png';
                $data['avatar_url'] = asset('assets/common/img/avatar/default.png');
            }

            $data['mailSettings'] = array(
                'MAIL_MAILER' => env('MAIL_MAILER'),
                'MAIL_HOST' => env('MAIL_HOST'),
                'MAIL_PORT' => env('MAIL_PORT'),
                'MAIL_USERNAME' => env('MAIL_USERNAME'),
                'MAIL_PASSWORD' => env('MAIL_PASSWORD'),
                'MAIL_ENCRYPTION' => env('MAIL_ENCRYPTION'),
                'MAIL_FROM_ADDRESS' => env('MAIL_FROM_ADDRESS'),
                'MAIL_FROM_NAME' => env('MAIL_FROM_NAME'),
            );

            $data['demoMode'] = config('custom.demo_mode');

            return $this->adapter->toApiResponse('Settings are fetched successfully', $data, CoreConstants::STATUS_CODE_SUCCESS);
        } catch (\Throwable $throwable) {
            Log::error($throwable->getMessage());

            return $this->adapter->toApiResponse('Something went wrong', $throwable->getMessage(), CoreConstants::STATUS_CODE_ERROR);
        }
    }

    private function getSettingValue(int $key, $default)
    {
        $setting = $this->settingRepository->getByKey($key, array('setting_value'));

        return $setting !== null ? $setting->setting_value : $default;
    }

    private function toBool($value): bool
    {
        return $value === true || $value === 'true' || $value === 1 || $value === '1';
    }

    private function resolveMediaUrl(?string $path): ?string
    {
        if ($path === null || $path === '') {
            return null;
        }

        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return $path;
        }

        return $this->mediaStorage->resolveUrl(config('filesystems.media_disk', 'minio'), $path);
    }
}
