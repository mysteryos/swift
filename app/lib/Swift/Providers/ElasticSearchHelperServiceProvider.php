<?php

namespace Swift\Providers;
use Illuminate\Support\ServiceProvider;
use Swift\Services\ElasticSearchHelper;
 
class ElasticSearchHelperServiceProvider extends ServiceProvider
{
    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->app['ElasticSearchHelper'] = $this->app->share(function ($app) {
            return new ElasticSearchHelper();
        });
    }
}