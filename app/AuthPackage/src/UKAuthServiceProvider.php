<?php

namespace VATSIMUK\Auth\Remote;

use Illuminate\Support\ServiceProvider;

class UKAuthServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom(
            __DIR__ . '/../config/ukauth.php',
            'ukauth'
        );
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        /*
         * Note: It is preferred that the config is not published. If changed are required to the default,
         * publish the config and then delete any value you are not overriding.
         */
        $this->publishes([
            __DIR__ . '/../config/ukauth.php' => config_path('ukauth.php'),
        ], 'vatsimuk-auth-package');
    }
}
