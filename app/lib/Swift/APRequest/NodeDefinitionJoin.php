<?php
/*
 * Name: A&P Request Node Definition Join
 * Description: Provides functions to handle Node Definition Joins
 */

NameSpace Swift\APRequest;

Use \SwiftApproval;
Use \SwiftWorkflowActivity;

Class NodeDefinitionJoin {
    
    /*
     * Calls to undefined static functions, routes to here
     */
    public static function __callStatic($method, $args) {
        /*
         * To Category Manager routing
         */
        if(strpos($method,'cmanrouteToCman') !== false)
        {
            return self::cmanrouting($args[0], str_replace('cmanrouteToCman','',$method));
        }
        
        /*
         * To Exec routing
         */
        if(strpos($method,'ToExec') !== false)
        {
            return self::execrouting($args[0]);
        }
        
        /*
         * To Customer Care Routing
         */
        if(strpos($method,'ToCcare') !== false)
        {
            return self::ccarerouting($args[0], str_replace('exectoccare','',strtolower($method)));
        }
        
        /*
         * To Delivery Routing
         */
        if(strpos($method,'ToDelivery') !== false)
        {
            return self::deliveryrouting();
        }
        
        echo "unknown method " . $method;
        return false;
    }    
    
    public static function startToPrep($nodeActivity)
    {
        return true;
    }
    
    public static function prepToCmanroute($nodeActivity)
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
    
    /*
     * All Cat Man Filters
     */
        
    private static function cmanrouting($nodeActivity,$productCategory)
    {
        $apr = $nodeActivity->workflowActivity()->first()->workflowable()->first();
        
        if(count($apr))
        {
            $productscount = $apr->product()->whereHas('jdeproduct',function($q) use ($productCategory){
                                return $q->where('SRP3','=',strtoupper($productCategory));
                        })->count();
            if($productscount > 0)
            {
                return true;
            }
        }
        
        return false;        
    }
    
    
    /*
     * All Customer Care Filters
     */
    
    private static function ccarerouting($nodeActivity,$customerCategory)
    {
        $workflow = $nodeActivity->workflowActivity()->first();
        $apr = $workflow->workflowable()->first();
        
        if(count($apr))
        {
            /*
             * Check if all products have been rejected
             */
            $productcount = $apr->product()->count();
            $rejectedproductcount = $apr->product()->whereHas('approval',function($q){
                return $q->where('type','=',SwiftApproval::APR_EXEC,'AND')->where('approved','=',SwiftApproval::REJECTED);
            })->count();
            if($productcount == $rejectedproductcount)
            {
                //All products have been rejected
                //Update Workflow as Rejected
                $workflow->status = SwiftWorkflowActivity::REJECTED;
                $workflow->save();
                NodeMail::sendCancelledMail($apr);
                return false;
            }
            else
            {
                $customer = $apr->customer()->first();
                if(count($customer) && strtolower($customer->AC09) == strtolower($customerCategory))
                {
                    return true;
                }
                /*
                 * Send to Others if we don't have any more
                 */
                if(strtolower($customerCategory) === "others")
                {
                    //Check if definition node for customer category exists
                    $nodeDefinitionCount = \SwiftNodeDefinition::where('workflow_type_id','=',2)
                                            ->where('name','=','apr_customercare_'.strtolower($customer->AC09),'AND')
                                            ->count();
                    if($nodeDefinitionCount === 0)
                    {
                        return true;
                    }
                }
            }
        }
        
        return false;          
    }
    
    /*
     * All Delivery routing
     */
    
    private static function deliveryrouting()
    {
        return true;
    }
    
    /*
     * From Cat Man To Exec
     * Description: Check if all 
     */
    private static function execrouting($nodeActivity)
    {
        $workflow = $nodeActivity->workflowActivity()->first();
        $apr = $workflow->workflowable()->first();
        
        if(count($apr))
        {
            $productcount = $apr->product()->count();
            $rejectedproductcount = $apr->product()->whereHas('approval',function($q){
                return $q->where('type','=',SwiftApproval::APR_CATMAN,'AND')->where('approved','=',SwiftApproval::REJECTED);
            })->count();
            
            if($productcount == $rejectedproductcount)
            {
                //All products have been rejected
                $workflow->status = SwiftWorkflowActivity::REJECTED;
                $workflow->save();
                NodeMail::sendCancelledMail($apr);
                return false;
            }
            
            $approvedproductcount = $apr->product()->whereHas('approval',function($q){
                return $q->where('type','=',SwiftApproval::APR_CATMAN,'AND')->where('approved','!=',SwiftApproval::PENDING);
            })->count();
            
            if($productcount == $approvedproductcount)
            {
                //All products have been processed for that exec
                return true;
            }
        }
        
        return false;
    }
    
    public static function deliveryToEnd($nodeActivity)
    {
        return true;
    }
}