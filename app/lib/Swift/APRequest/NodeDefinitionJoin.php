<?php
/*
 * Name: A&P Request Node Definition Join
 * Description: Provides functions to handle Node Definition Joins
 */

NameSpace Swift\APRequest;

Use \SwiftApproval;
Use \SwiftWorkflowActivity;

Class NodeDefinitionJoin {
    
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
        if(strpos($method,'execToCcare') !== false)
        {
            return self::cmanrouting($args[0], str_replace('execToCcare','',$method));
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
    
    public static function startToPreparation($nodeActivity)
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
     * Cat Man: Drinks
     */
//    public function cmanrouteToCmandrk($nodeActivity)
//    {
//        return self::cmanrouting($nodeActivity,"drk");
//    }
    
    /*
     * Cat Man: Food
     */
//    public function cmanrouteToCmanfod($nodeActivity)
//    {
//        return self::cmanrouting($nodeActivity,"fod");
//    }
    
    /*
     * Cat Man: Spirits
     */
//    public function cmanrouteToCmanspi($nodeActivity)
//    {
//        return self::cmanrouting($nodeActivity,"spi");
//    }
    
    /*
     * Cat Man: Wines
     */
//    public function cmanrouteToCmanwin($nodeActivity)
//    {
//        return self::cmanrouting($nodeActivity,"win");
//    }    
        
    /*
     * Cat Man: Cigarette
     */
//    public function cmanrouteToCmancig($nodeActivity)
//    {
//        return self::cmanrouting($nodeActivity,"cig");
//    }    
    
    /*
     * Cat Man: Nespresso
     */
//    public function cmanrouteToCmannes($nodeActivity)
//    {
//        return self::cmanrouting($nodeActivity,"nes");
//    }
    
    /*
     * All Cat Man Filters
     */
        
    private static function cmanrouting($nodeActivity,$productCategory)
    {
        $apr = $nodeActivity->workflowActivity()->first()->workflowable()->first();
        
        if(count($apr))
        {
            $products = $apr->product()->with('product.jdeproduct')->get();
            if(count($products))
            {
                foreach($products as $p)
                {
                    if(strtolower(trim($p->SRP3)) == $productCategory)
                    {
                        return true;
                    }
                }
            }
        }
        
        return false;        
    }
    
    /*
     * Customer Care: Corporate
     */
    
//    public function execToCcaredb($nodeActivity)
//    {
//        return self::ccarerouting($nodeActivity,"db");
//    }
    
    /*
     * Customer Care: Corporate
     */
    
//    public function execToCcareco($nodeActivity)
//    {
//        return self::ccarerouting($nodeActivity,"co");
//    }
    
    /*
     * Customer Care: Corporate
     */
    
//    public function execToCcareex($nodeActivity)
//    {
//        return self::ccarerouting($nodeActivity,"co");
//    }    
    
    /*
     * All Customer Care Filters
     */
    
    private static function ccarerouting($nodeActivity,$custommerCategory)
    {
        $workflow = $nodeActivity->workflowActivity()->first();
        $apr = $workflow->workflowable()->first();
        
        if(count($apr))
        {
            /*
             * Check if all products have been rejected
             */
            $products = $apr->products()->get();
            $rejectedproducts = $apr->products()->whereHas('approval',function($q){
                return $q->where('type','=',SwiftApproval::APR_EXEC,'AND')->where('approved','=',SwiftApproval::REJECTED);
            })->get();
            if(count($products) == count($rejectedproducts))
            {
                //All products have been rejected
                //Update Workflow as Rejected
                $workflow->status = SwiftWorkflowActivity::REJECTED;
                $workflow->save();
                return false;
            }
            else
            {
                $customer = $apr->customer()->get();
                if(count($customer) && strtolower($customer->AC09) == $customerCategory)
                {
                    return true;
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
            $products = $apr->products()->get();
            $rejectedproducts = $apr->products()->whereHas('approval',function($q){
                return $q->where('type','=',SwiftApproval::APR_CATMAN,'AND')->where('approved','=',SwiftApproval::REJECTED);
            })->get();
            if(count($products) == count($rejectedproducts))
            {
                //All products have been rejected
                $workflow->status = SwiftWorkflowActivity::REJECTED;
                $workflow->save();
                return false;
            }
        }
        
        return true;
    }
    
    public static function deliveryToEnd($nodeActivity)
    {
        return true;
    }
}