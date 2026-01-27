<?php

/**
 * This is NOT a freeware, use is subject to license terms.
 */

declare(strict_types=1);

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

/**
 * 应用服务
 *
 * @author Tongle Xu <xutongle@msn.com>
 */
class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // 注册系统设置服务
        $this->app->singleton(\App\Services\SettingManagerService::class, function () {
            return new \App\Services\SettingManagerService;
        });

        if ($this->app->environment('local') && class_exists(\Laravel\Telescope\TelescopeServiceProvider::class)) {
            $this->app->register(\Laravel\Telescope\TelescopeServiceProvider::class);
            $this->app->register(TelescopeServiceProvider::class);
        }
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        \Illuminate\Support\Carbon::setLocale('zh');
        \Illuminate\Http\Resources\Json\JsonResource::withoutWrapping();
        \Illuminate\Database\Eloquent\Model::shouldBeStrict(! $this->app->isProduction());
        \Laravel\Sanctum\Sanctum::usePersonalAccessTokenModel(\App\Models\PersonalAccessToken::class);
        \Illuminate\Database\Eloquent\Relations\Relation::enforceMorphMap(config('morph_maps'));
    }
}
