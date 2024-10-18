<?php

namespace App\Providers;

use Str;
use URL;
use Config;
use Schema;
use App\Models\Experience;
use App\Rules\GoogleReCaptcha;
use App\Services\AboutService;
use App\Services\AdminService;
use App\Services\SkillService;
use App\Services\MessageService;
use App\Services\ProjectService;
use App\Services\ServiceService;
use App\Services\SettingService;
use App\Services\VisitorService;
use App\Services\FrontendService;
use App\Services\EducationService;
use App\Services\ExperienceService;
use Illuminate\Support\ServiceProvider;
use App\Services\PortfolioConfigService;
use App\Services\Contracts\AboutInterface;
use App\Services\Contracts\AdminInterface;
use App\Services\Contracts\SkillInterface;
use App\Services\Contracts\MessageInterface;
use App\Services\Contracts\ProjectInterface;
use App\Services\Contracts\ServiceInterface;
use App\Services\Contracts\SettingInterface;
use App\Services\Contracts\VisitorInterface;
use App\Services\Contracts\FrontendInterface;
use App\Services\Contracts\EducationInterface;
use App\Services\Contracts\ExperienceInterface;
use App\Services\Contracts\PortfolioConfigInterface;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        if ($this->app->isLocal()) {
            $this->app->register(\Barryvdh\LaravelIdeHelper\IdeHelperServiceProvider::class);
        }

        $this->app->bind(SettingInterface::class, SettingService::class);
        $this->app->bind(AboutInterface::class, AboutService::class);
        $this->app->bind(AdminInterface::class, AdminService::class);
        $this->app->bind(PortfolioConfigInterface::class, PortfolioConfigService::class);
        $this->app->bind(EducationInterface::class, EducationService::class);
        $this->app->bind(ExperienceInterface::class, ExperienceService::class);
        $this->app->bind(SkillInterface::class, SkillService::class);
        $this->app->bind(ProjectInterface::class, ProjectService::class);
        $this->app->bind(ServiceInterface::class, ServiceService::class);
        $this->app->bind(FrontendInterface::class, FrontendService::class);
        $this->app->bind(VisitorInterface::class, VisitorService::class);
        $this->app->bind(MessageInterface::class, MessageService::class);
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        if ((Config::get('app.url') !== 'http://localhost') && (Str::contains(Config::get('app.url'), 'https://'))) {
            URL::forceScheme('https');
        }
        
        Schema::defaultStringLength(191);

        $this->app->validator->extend('google_recaptcha', function ($attribute, $value, $parameters, $validator) {
            $rule = new GoogleReCaptcha;

            // Validate reCAPTCHA
            $rule->validate($attribute, $value, function ($message) use ($validator, $attribute) {
                $validator->errors()->add($attribute, $message);
            });
            // Return true to success
            return true;
        });
    }
}
