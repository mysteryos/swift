<?php

namespace Swift\Providers;
use Illuminate\Support\ServiceProvider;
use Swift\Services\SalesCommission;
 
class SalesCommissionServiceProvider extends ServiceProvider
{
    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->app['SalesCommission'] = $this->app->share(function ($app) {
            return new SalesCommission();
        });
    }
}