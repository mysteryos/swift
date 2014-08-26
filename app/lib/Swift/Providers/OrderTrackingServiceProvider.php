<?php

namespace Swift\Providers;
use Illuminate\Support\ServiceProvider;
use Swift\Services\OrderProcess;
 
class OrderTrackingServiceProvider extends ServiceProvider
{
    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->app['OrderTracking'] = $this->app->share(function ($app) {
            return new OrderTracking();
        });
    }
}