<?php

declare(strict_types=1);

namespace Src\Modules\About\Repositories;

use Illuminate\Database\Eloquent\Model;
use Src\Shared\Contracts\BaseRepositoryInterface;

interface AboutRepositoryInterface extends BaseRepositoryInterface
{
    public function first(): ?Model;
}
