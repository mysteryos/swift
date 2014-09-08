<?php

namespace Swift\Providers;
use Illuminate\Support\ServiceProvider;
use Swift\Services\Flag;
 
class FlagServiceProvider extends ServiceProvider
{
    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->app['Flag'] = $this->app->share(function ($app) {
            return new Flag();
        });
    }
}