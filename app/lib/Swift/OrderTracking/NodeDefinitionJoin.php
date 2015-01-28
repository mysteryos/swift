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
    
    public static function receptionToReceptionconsumer($nodeActivity)
    {
        $order = $nodeActivity->workflowActivity()->first()->workflowable()->first();
        
        if(count($order))
        {
            if(in_array($order->business_unit,array(\SwiftOrder::SCOTT_CONSUMER,\SwiftOrder::SEBNA)))
            {
                return true;
            }
        }
        
        return false;
        
    }
    
    public static function receptionToReceptionhealth($nodeActivity)
    {
        $order = $nodeActivity->workflowActivity()->first()->workflowable()->first();
        
        if(count($order))
        {
            if(in_array($order->business_unit,array(\SwiftOrder::SCOTT_HEALTH)))
            {
                return true;
            }
        }
        
        return false;        
    }
    
    public static function receptionhealthToEnd($nodeActivity)
    {
        return true;
    }
    
    public static function receptionconsumerToEnd($nodeActivity)
    {
        return true;
    }
    
}