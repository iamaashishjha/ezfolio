<?php

declare(strict_types=1);

namespace Src\Modules\About\Adapters;

use Src\Modules\About\DTO\AboutData;

final class AboutDataAdapter
{
    public function adapt($source): array
    {
        if ($source instanceof AboutData) {
            return $source->toArray();
        }

        if (is_array($source)) {
            return $source;
        }

        if (is_object($source) && method_exists($source, 'toArray')) {
            return $source->toArray();
        }

        return array();
    }
}
