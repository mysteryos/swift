<?php

namespace Swift\Providers;
use Illuminate\Support\ServiceProvider;
use Swift\Services\Subscription;
 
class SubscriptionServiceProvider extends ServiceProvider
{
    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->app['Subscription'] = $this->app->share(function ($app) {
            return new Subscription();
        });
    }
}