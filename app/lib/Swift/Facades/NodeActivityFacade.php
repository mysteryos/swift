<?php

namespace Swift\Facades;
use Illuminate\Support\Facades\Facade;
 
class NodeActivityFacade extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'NodeActivity';
    }
}