<?php

namespace Swift\Providers;
use Illuminate\Support\ServiceProvider;
use Swift\Services\NodeDefinition;
 
class NodeDefinitionServiceProvider extends ServiceProvider
{
    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->app['NodeDefinition'] = $this->app->share(function ($app) {
            return new NodeDefinition();
        });
    }
}