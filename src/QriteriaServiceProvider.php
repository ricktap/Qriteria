<?php

namespace RickTap\Qriteria;

use Illuminate\Support\ServiceProvider;

class QriteriaServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     */
    public function boot()
    {
        $this->publishes([
            __DIR__.'/../config/qriteria.php' => config_path('qriteria.php'),
        ], 'config');
    }

    /**
     * Register the application services.
     */
    public function register()
    {
        $this->mergeConfigFrom(__DIR__.'/../config/qriteria.php', 'qriteria');
    }
}
