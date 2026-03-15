<?php

declare(strict_types=1);

namespace Src\Modules\Settings\Http\Resources;

use Src\Shared\Http\Resources\ApiResource;

final class SettingsResource extends ApiResource
{
    protected function message(): string
    {
        return (string) ($this->resource['message'] ?? parent::message());
    }

    protected function status(): int
    {
        return (int) ($this->resource['status'] ?? parent::status());
    }

    protected function payload($request)
    {
        return $this->resource['payload'] ?? null;
    }

    protected function meta($request): array
    {
        return is_array($this->resource['meta'] ?? null) ? $this->resource['meta'] : array();
    }
}
