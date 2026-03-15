<?php

declare(strict_types=1);

namespace Src\Modules\Settings\Repositories;

use App\Models\Setting;
use Illuminate\Database\Eloquent\Model;
use Src\Shared\Persistence\BaseRepository;

final class SettingRepository extends BaseRepository implements SettingRepositoryInterface
{
    public function __construct(Setting $model)
    {
        parent::__construct($model);
    }

    public function getByKey(int $key, array $select = array('*')): ?Model
    {
        return $this->newQuery()->select($select)->where('setting_key', $key)->first();
    }

    public function upsertByKey(int $key, $value, $default = null): Model
    {
        $attributes = array(
            'setting_key' => $key,
            'setting_value' => $value,
        );

        if ($default !== null) {
            $attributes['default_value'] = $default;
        }

        return $this->newQuery()->updateOrCreate(array('setting_key' => $key), $attributes);
    }
}
