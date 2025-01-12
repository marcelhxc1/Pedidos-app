<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        if (extension_loaded('newrelic')) {
            newrelic_set_appname(env('NEW_RELIC_APP_NAME', 'LaravelApp'));
        }
    }
}
