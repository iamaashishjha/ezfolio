<?php

declare(strict_types=1);

namespace Src\Modules\Settings\Services;

use App\Helpers\CoreConstants;
use App\Support\EnvEditor;
use Illuminate\Support\Facades\Log;
use Src\Modules\Settings\Adapters\SettingsDataAdapter;

final class StoreMailSettingsService
{
    public function __construct(private SettingsDataAdapter $adapter)
    {
    }

    public function handle(array $data): array
    {
        try {
            $ok = EnvEditor::setMany(array(
                'MAIL_MAILER' => (string) $data['MAIL_MAILER'],
                'MAIL_HOST' => (string) $data['MAIL_HOST'],
                'MAIL_PORT' => (string) $data['MAIL_PORT'],
                'MAIL_USERNAME' => (string) $data['MAIL_USERNAME'],
                'MAIL_PASSWORD' => (string) $data['MAIL_PASSWORD'],
                'MAIL_ENCRYPTION' => (string) $data['MAIL_ENCRYPTION'],
                'MAIL_FROM_ADDRESS' => (string) ($data['MAIL_FROM_ADDRESS'] ?? ''),
                'MAIL_FROM_NAME' => (string) ($data['MAIL_FROM_NAME'] ?? ''),
            ));

            return $ok
                ? $this->adapter->toApiResponse('Mail setting is successfully updated', null, CoreConstants::STATUS_CODE_SUCCESS)
                : $this->adapter->toApiResponse('Something went wrong', null, CoreConstants::STATUS_CODE_ERROR);
        } catch (\Throwable $throwable) {
            Log::error($throwable->getMessage());
            return $this->adapter->toApiResponse('Something went wrong', $throwable->getMessage(), CoreConstants::STATUS_CODE_ERROR);
        }
    }
}
