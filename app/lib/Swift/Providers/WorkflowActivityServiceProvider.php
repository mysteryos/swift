<?php

namespace Swift\Providers;
use Illuminate\Support\ServiceProvider;
use Swift\Services\WorkflowActivity;
 
class WorkflowActivityServiceProvider extends ServiceProvider
{
    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->app['WorkflowActivity'] = $this->app->share(function ($app) {
            return new WorkflowActivity();
        });
    }
}