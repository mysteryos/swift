<?php

namespace Swift\Providers;
use Illuminate\Support\ServiceProvider;
use Swift\Services\APRequestHelper;
 
class APRequestHelperServiceProvider extends ServiceProvider
{
    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->app['APRequestHelper'] = $this->app->share(function ($app) {
            return new APRequestHelper();
        });
    }
}