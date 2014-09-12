<?php

namespace Swift\Providers;
use Illuminate\Support\ServiceProvider;
use Swift\Services\Comment;
 
class CommentServiceProvider extends ServiceProvider
{
    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->app['Comment'] = $this->app->share(function ($app) {
            return new Comment();
        });
    }
}