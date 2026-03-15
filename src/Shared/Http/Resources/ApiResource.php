<?php

declare(strict_types=1);

namespace Src\Shared\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

abstract class ApiResource extends JsonResource
{
    public function toArray($request): array
    {
        return array(
            'message' => $this->message(),
            'payload' => $this->payload($request),
            'status' => $this->status(),
            'meta' => $this->meta($request),
        );
    }

    protected function message(): string
    {
        return 'Success';
    }

    protected function status(): int
    {
        return 200;
    }

    protected function payload($request)
    {
        return $this->resource;
    }

    protected function meta($request): array
    {
        return array();
    }
}
