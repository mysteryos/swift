<?php

namespace Swift\Providers;
use Illuminate\Support\ServiceProvider;
use Swift\Services\OcrTask;
 
class OcrTaskServiceProvider extends ServiceProvider
{
    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->app['OcrTask'] = $this->app->share(function ($app) {
            return new OcrTask();
        });
    }
}