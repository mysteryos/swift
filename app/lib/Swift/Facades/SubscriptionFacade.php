<?php

namespace Swift\Facades;
use Illuminate\Support\Facades\Facade;
 
class SubscriptionFacade extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'Subscription';
    }
}