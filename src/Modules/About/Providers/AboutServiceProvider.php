<?php

declare(strict_types=1);

namespace Src\Modules\About\Providers;

use Illuminate\Support\ServiceProvider;
use Src\Modules\About\Repositories\AboutRepository;
use Src\Modules\About\Repositories\AboutRepositoryInterface;

final class AboutServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(AboutRepositoryInterface::class, AboutRepository::class);
    }

    public function boot(): void
    {
        $this->loadRoutesFrom(__DIR__ . '/../Routes/api.php');
    }
}
