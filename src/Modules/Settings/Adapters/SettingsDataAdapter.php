<?php

declare(strict_types=1);

namespace Src\Modules\Settings\Adapters;

final class SettingsDataAdapter
{
    public function toApiResponse(string $message, $payload, int $status, array $meta = array()): array
    {
        return array(
            'message' => $message,
            'payload' => $payload,
            'status' => $status,
            'meta' => $meta,
        );
    }
}
