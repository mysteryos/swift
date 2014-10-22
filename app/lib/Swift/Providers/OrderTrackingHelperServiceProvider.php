<?php

namespace Swift\Providers;
use Illuminate\Support\ServiceProvider;
use Swift\Services\OrderTrackingHelper;
 
class OrderTrackingHelperServiceProvider extends ServiceProvider
{
    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->app['OrderTrackingHelper'] = $this->app->share(function ($app) {
            return new OrderTrackingHelper();
        });
    }
}