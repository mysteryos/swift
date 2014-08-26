<?php
/*
 * Name: Swift Message facade
 * Description:
 */

namespace Swift\Facades;
use Illuminate\Support\Facades\Facade;
 
class MessageFacade extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'Message';
    }
}

