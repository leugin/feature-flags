<?php

namespace Miguel\FeatureFlags\Provider;

use Illuminate\Support\ServiceProvider;
use Miguel\FeatureFlags\Contracts\FeatureFlagService;

class FeatureFlagServiceProvider extends ServiceProvider
{


    public function register()
    {

        $this->app->singleton(FeatureFlagService::class, function () {
            $selectedDriver = config('feature-flags.driver');
            $selecteProvider = config('feature-flags.providers.' . $selectedDriver);
            $params = config('feature-flags.params.' . $selectedDriver) ?? [];
            return new $selecteProvider($params);
        });

    }

    public function boot()
    {
        $this->publishes([
            __DIR__ . '/../../config/feature-flags.php' => config_path('feature-flags.php'),
        ], 'config');
    }
}