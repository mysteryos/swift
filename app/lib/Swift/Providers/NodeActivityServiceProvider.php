<?php

namespace Swift\Providers;
use Illuminate\Support\ServiceProvider;
use Swift\Services\NodeActivity;
 
class NodeActivityServiceProvider extends ServiceProvider
{
    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->app['NodeActivity'] = $this->app->share(function ($app) {
            return new NodeActivity();
        });
    }
}