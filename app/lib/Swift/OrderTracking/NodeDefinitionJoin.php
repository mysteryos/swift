<?php
/*
 * Name: Order Tracking
 * Description: Provides functions to handle Node Definition Joins
 */

NameSpace Swift\OrderTracking;

Class NodeDefinitionJoin {
    
    public static function startToPrep($nodeActivity)
    {
        return true;
    }
    
    public static function prepToTransit($nodeActivity)
    {
        return true;
    }
    
    public static function transitToCustoms($nodeActivity)
    {
        return true;      
    }
    
    public static function transitToCosting($nodeActivity)
    {
        return true;       
    }
    
    public static function customsToPickup($nodeActivity)
    {
        return true;       
    }
    
    public static function pickupToReception($nodeActivity)
    {
        return true;
    }
    
    public static function costingToReception($nodeActivity)
    {
        return true;
    }
    
    public static function receptionToEnd($nodeActivity)
    {
        return true;
    }
    
}