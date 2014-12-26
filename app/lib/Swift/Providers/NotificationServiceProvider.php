<?php

namespace Swift\Providers;
use Illuminate\Support\ServiceProvider;
use Swift\Services\Notification;
 
class NotificationServiceProvider extends ServiceProvider
{
    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->app['Notification'] = $this->app->share(function ($app) {
            return new Notification();
        });
    }
}