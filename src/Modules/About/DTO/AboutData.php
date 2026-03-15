<?php

declare(strict_types=1);

namespace Src\Modules\About\DTO;

final class AboutData
{
    public function __construct(private array $attributes)
    {
    }

    public static function fromArray(array $attributes): self
    {
        return new self($attributes);
    }

    public function toArray(): array
    {
        return $this->attributes;
    }
}
