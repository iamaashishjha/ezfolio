<?php

declare(strict_types=1);

namespace Src\Modules\About\Repositories;

use App\Models\About;
use Illuminate\Database\Eloquent\Model;
use Src\Shared\Persistence\BaseRepository;

final class AboutRepository extends BaseRepository implements AboutRepositoryInterface
{
    public function __construct(About $model)
    {
        parent::__construct($model);
    }

    public function first(): ?Model
    {
        return $this->newQuery()->first();
    }
}
