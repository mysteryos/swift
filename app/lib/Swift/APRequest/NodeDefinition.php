<?php
/*
 * Name: A&P Request Node Definition
 * Description: Provides functions to handle Node Definition
 */

NameSpace Swift\APRequest;

Use SwiftApproval;
Use SwiftAPOrder;
Use SwiftDelivery;

Class NodeDefinition {
    
    public static function __callStatic($method, $args) {
        /*
         * Category Manager Nodes
         */
        
        if(strpos($method,'aprCatapproval') !== false)
        {
            return self::aprCatapproval($args[0],str_replace('aprCatapproval','',$method));
        }
        
        /*
         * Customer Care
         */
        
        if(strpos($method,'aprCustomercare') !== false)
        {
            return self::aprCustomercare($args[0]);
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
            $countapproval = $apr->approval()->where('type','=',SwiftApproval::APR_REQUESTER)->count();
            if($countapproval > 0)
            {
                return true;
            }
        }
        return false;
    }
    
    public static function aprCatapprovalRoute($nodeActivity)
    {
        return true;
    }
    
    private static function aprCatapproval($nodeActivity,$productCategory)
    {
        $apr = $nodeActivity->workflowActivity()->first()->workflowable()->first();
        
        if(count($apr))
        {
            $productscount = $apr->product()->whereHas('jdeproduct',function($q) use ($productCategory){
                                return $q->where('SRP3','=',strtoupper($productCategory));
                        })->count();
            
            $approvalscount = $apr->product()->whereHas('approval',function($q){
                                    return $q->where('type','=',SwiftApproval::APR_CATMAN,'AND')->where('approved','!=',SwiftApproval::PENDING);
                            })->whereHas('jdeproduct',function($q) use ($productCategory){
                                return $q->where('SRP3','=',strtoupper($productCategory));
                            })->count();
            
            if($productscount > 0 && $productscount == $approvalscount)
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
            $products = $apr->product()->get();
            $approvals = $apr->product()->whereHas('approval',function($q){
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
    
    public static function aprCustomerRoute($nodeActivity)
    {
        return true;
    }
    
    private static function aprCustomercare($nodeActivity)
    {
        $apr = $nodeActivity->workflowActivity()->first()->workflowable()->first();
        
        if(count($apr))
        {
            $order = $apr->order()->get();
            if(count($order))
            {
                foreach($order as $o)
                {
                    if($o->ref != "" && $o->status == \SwiftAPOrder::FILLED)
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
            $delivery = $apr->delivery()->get();
            if(count($delivery))
            {
                foreach($delivery as $d)
                {
                    if($d->status != SwiftDelivery::PENDING && (int)$d->invoice_number != 0)
                    {
                        //If delivered, should have invoice recipient
                        if($d->status == SwiftDelivery::DELIVERED  && $d->invoice_recipient != "")
                        {
                            return true;
                        }
                        //If cancelled, nothing else is needed
                        if($d->status == SwiftDelivery::CANCELLED)
                        {
                            return true;
                        }
                    }
                }
            }
        }
        
        return false;
    }
    
    public static function aprEnd($nodeActivity)
    {
        return true;
    }
}