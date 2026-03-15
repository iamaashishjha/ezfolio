<?php

declare(strict_types=1);

namespace Src\Providers;

use Illuminate\Support\ServiceProvider;
use Src\Modules\About\Providers\AboutServiceProvider;
use Src\Modules\Auth\Providers\AuthModuleServiceProvider;
use Src\Modules\Blog\Providers\BlogModuleServiceProvider;
use Src\Modules\Portfolio\Providers\PortfolioModuleServiceProvider;
use Src\Modules\Settings\Providers\SettingsModuleServiceProvider;

final class ModuleServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->register(AuthModuleServiceProvider::class);
        $this->app->register(SettingsModuleServiceProvider::class);
        $this->app->register(PortfolioModuleServiceProvider::class);
        $this->app->register(BlogModuleServiceProvider::class);
        $this->app->register(AboutServiceProvider::class);
    }
}
