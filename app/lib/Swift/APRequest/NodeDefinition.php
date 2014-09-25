<?php
/*
 * Name: A&P Request Node Definition
 * Description: Provides functions to handle Node Definition
 */

NameSpace Swift\APRequest;

Use SwiftApproval;
Use SwiftAPOrder;

Class NodeDefinition {
    
    public function __call($method, $args) {
        /*
         * Category Manager Nodes
         */
        
        if(strpos($method,'aprCatapproval') !== false)
        {
            return self::aprCatapproval($args[0],str_replace('aprCatapproval','',$method));
        }
    }
    
    
    public static function aprStart($nodeActivity)
    {
        return true;
    }
    
    public static function aprPreparation($nodeActivity)
    {
        $apr = $nodeActivity->workflowActivity()->first()->workflowable()->first();
        
        if(count($apr))
        {
            $approval = $apr->approval()->where('type','=',SwiftApproval::APR_REQUESTER)->get();
            if(count($approval))
            {
                return true;
            }
        }
        return false;
    }
    
    private static function aprCatapproval($nodeActivity,$productCategory)
    {
        $apr = $nodeActivity->workflowActivity()->first()->workflowable()->first();
        
        if(count($apr))
        {
            $products = $apr->products()->whereHas('jdeproduct',function($q){
                return $q->where('SRP3','=',strtoupper($productCategory));
            })->get();
            
            $approvals = $apr->products()->whereHas('approval',function($q){
                return $q->where('type','=',SwiftApproval::APR_CATMAN,'AND')->where('approved','!=',SwiftApproval::PENDING);
            })->whereHas('jdeproduct',function($q){
                return $q->where('SRP3','=',strtoupper($productCategory));
            })->get();
            
            if(count($products) == count($approvals))
            {
                //Total count tallies
                //All approvals are done and done
                return true;
            }
        }
        
        return false;
    }
    
    public static function aprExecapproval($nodeActivity)
    {
        $apr = $nodeActivity->workflowActivity()->first()->workflowable()->first();
        
        if(count($apr))
        {
            $products = $apr->products()->get();
            $approvals = $apr->products()->whereHas('approval',function($q){
                return $q->where('type','=',SwiftApproval::APR_EXEC,'AND')->where('approved','!=',SwiftApproval::PENDING);
            })->get();
            if(count($products) == count($approvals))
            {
                //Total count tallies
                //All approvals are done and done
                return true;
            }
        }
        
        return false;        
    }
    
    public static function aprCustomercare($nodeActivity)
    {
        $apr = $nodeActivity->workflowActivity()->first()->workflowable()->first();
        
        if(count($apr))
        {
            $order = $apr->order()->get();
            if(count($order))
            {
                foreach($order as $o)
                {
                    if($o->ref != "" && $o->status = \SwiftAPOrder::FILLED)
                    {
                        return true;
                    }
                }
            }
        }
        
        return false;
    }
    
    public static function aprDelivery($nodeActivity)
    {
        $apr = $nodeActivity->workflowActivity()->first()->workflowable()->first();
        
        if(count($apr))
        {
            
        }
        
        return false;
    }
    
    public static function aprEnd($nodeActivity)
    {
        
    }
}