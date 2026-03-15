<?php

declare(strict_types=1);

namespace Src\Modules\Settings\Services;

use App\Helpers\CoreConstants;
use App\Support\EnvEditor;
use Illuminate\Support\Facades\Log;
use Src\Modules\Settings\Adapters\SettingsDataAdapter;
use Src\Modules\Settings\Repositories\SettingRepositoryInterface;

final class UpsertSettingService
{
    public function __construct(
        private SettingRepositoryInterface $settingRepository,
        private SettingsDataAdapter $adapter
    ) {
    }

    public function handle(array $data): array
    {
        try {
            if ((int) $data['setting_key'] === CoreConstants::SETTING__SITE_NAME) {
                $ok = EnvEditor::set('APP_NAME', (string) $data['setting_value']);

                return $ok
                    ? $this->adapter->toApiResponse('Site name is successfully updated', (string) $data['setting_value'], CoreConstants::STATUS_CODE_SUCCESS)
                    : $this->adapter->toApiResponse('Something went wrong', null, CoreConstants::STATUS_CODE_ERROR);
            }

            $record = $this->settingRepository->upsertByKey((int) $data['setting_key'], $data['setting_value']);

            return $this->adapter->toApiResponse('Data is saved successfully', $record, CoreConstants::STATUS_CODE_SUCCESS);
        } catch (\Throwable $throwable) {
            Log::error($throwable->getMessage());

            return $this->adapter->toApiResponse('Something went wrong', $throwable->getMessage(), CoreConstants::STATUS_CODE_ERROR);
        }
    }
}
