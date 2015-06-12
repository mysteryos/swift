<?php

namespace Swift\Providers;
use Illuminate\Support\ServiceProvider;
use Swift\Services\PusherChannel;
 
class PusherChannelServiceProvider extends ServiceProvider
{
    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->app['PusherChannel'] = $this->app->share(function ($app) {
            return new PusherChannel();
        });
    }
}