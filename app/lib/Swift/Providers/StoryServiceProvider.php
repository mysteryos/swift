<?php

namespace Swift\Providers;
use Illuminate\Support\ServiceProvider;
use Swift\Services\Story;
 
class StoryServiceProvider extends ServiceProvider
{
    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->app['Story'] = $this->app->share(function ($app) {
            return new Story();
        });
    }
}