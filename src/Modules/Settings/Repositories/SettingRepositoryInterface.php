<?php

declare(strict_types=1);

namespace Src\Modules\Settings\Repositories;

use Illuminate\Database\Eloquent\Model;
use Src\Shared\Contracts\BaseRepositoryInterface;

interface SettingRepositoryInterface extends BaseRepositoryInterface
{
    public function getByKey(int $key, array $select = array('*')): ?Model;

    public function upsertByKey(int $key, $value, $default = null): Model;
}
