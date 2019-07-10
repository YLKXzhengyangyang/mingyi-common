<?php

namespace Mingyi\Common;

use Predis\Client;

class ServiceProvider extends \Illuminate\Support\ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton('api', function () {
            return new Api();
        });

        $this->app->singleton('helper', function () {
            return new Helper();
        });
    }
}
