<?php

namespace Swift\Facades;
use Illuminate\Support\Facades\Facade;
 
class ElasticSearchHelperFacade extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'ElasticSearchHelper';
    }
}