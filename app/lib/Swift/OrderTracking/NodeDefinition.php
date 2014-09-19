<?php
/*
 * Name: Order Tracking Node Definition
 * Description: Provides functions to handle Node Definition
 */

NameSpace Swift\OrderTracking;

Class NodeDefinition {
    
    public static function otStart($nodeActivity)
    {
        return true;
    }
    
    public static function otPreparation($nodeActivity)
    {
        $order = $nodeActivity->workflowActivity()->first()->workflowable()->first();
        if(count($order))
        {
            //Check if bill of lading has been uploaded
            //If it is, Order is now in transit
            $docs = $order->document()->get();
            if(count($docs))
            {
                $docs = $docs->load('tag');
                foreach($docs as $d)
                {
                    $tagfound = false;
                    //Get Tags
                    if(count($d->tag))
                    {
                        foreach($d->tag as $tag)
                        {
                            if($tag->type == \SwiftTag::OT_BILL_OF_LADING)
                            {
                                $tagfound = true;
                                break;
                            }
                        }
                    }
                    if($tagfound)
                    {
                        break;
                    }
                }
                
                if($tagfound)
                {
                    /*
                     * Order process must have at least one purchase order attached to it
                     */
                    $order->load('purchaseOrder');
                    if(count($order->purchaseOrder))
                    {
                        return true;
                    }
                }
            }
        }

        return false;      
    }
    
    public static function otTransit($nodeActivity)
    {
        $order = $nodeActivity->workflowActivity()->first()->workflowable()->first();
        if(count($order))
        {
            //Check if bill of lading has been uploaded
            //If it is, Order is now in transit
            $docs = $order->document()->get();
            if(count($docs))
            {
                $docs = $docs->load('tag');
                foreach($docs as $d)
                {
                    $tagfound = false;
                    //Get Tags
                    if(count($d->tag))
                    {
                        foreach($d->tag as $tag)
                        {
                            if($tag->type == \SwiftTag::OT_NOTICE_OF_ARRIVAL)
                            {
                                $tagfound = true;
                                break;
                            }
                        }
                    }
                    if($tagfound)
                    {
                        /*
                         * Must Have at least 1 entry in freight
                         */
                        $order->load('freight');
                        if(count($order->freight))
                        {
                            return true;
                        }
                        break;
                    }
                }
            }
        }
        
        return false;         
    }
    
    public static function otCustoms($nodeActivity)
    {
        $order = $nodeActivity->workflowActivity()->first()->workflowable()->first();
        if(count($order))
        {
            //Check if bill of lading has been uploaded
            //If it is, Order is now in transit
            $docs = $order->document()->get();
            if(count($docs))
            {
                $docs = $docs->load('tag');
                foreach($docs as $d)
                {
                    $tagfound = false;
                    //Get Tags
                    if(count($d->tag))
                    {
                        foreach($d->tag as $tag)
                        {
                            if($tag->type == \SwiftTag::OT_BILL_OF_ENTRY)
                            {
                                $tagfound = true;
                                break;
                            }
                        }
                    }
                    if($tagfound)
                    {
                        /*
                         * Must have a customs entry with refernce and cleared status
                         */
                        $order->load('customsDeclaration');
                        if(count($order->customsDeclaration))
                        {
                            foreach($order->customsDeclaration as $c)
                            {
                                if($c->customs_reference != "" && $c->customs_status == \SwiftCustomsDeclaration::CLEARED)
                                {
                                    return true;
                                }
                            }
                        }
                        break;
                    }
                }
            }
        }
        
        return false;         
    }
    
    public static function otPickup($nodeActivity)
    {
        $order = $nodeActivity->workflowActivity()->first()->workflowable()->first();
        if(count($order))
        {
            /*
             * Check if freight is land and if pickup date has been set
             */
            $freight = $order->freight()->get();
            if(count($freight))
            {
                foreach($freight as $f)
                {
                    if($f->freight_type == \SwiftFreight::TYPE_LAND && $f->freight_etd != "")
                    {
                        return true;
                    }
                                
                    /*
                     * Check if incoterm is DAT/DAP, local pickup will arranged by foreign company
                     */
                    if((int)$f->incoterm !== 0 && in_array($f->incoterm,array(\SwiftFreight::INCOTERM_DAT,\SwiftFreight::INCOTERM_DAP)))
                    {
                        return true;
                    }
                }
            }
            
        }       
        
        return false;        
    }
    
    public static function otReception($nodeActivity)
    {
        $order = $nodeActivity->workflowActivity()->first()->workflowable()->first();
        if(count($order))
        {
            //Check if bill of lading has been uploaded
            //If it is, Order is now in transit
            $docs = $order->document()->get();
            if(count($docs))
            {
                $docs = $docs->load('tag');
                foreach($docs as $d)
                {
                    $tagfound = false;
                    //Get Tags
                    if(count($d->tag))
                    {
                        foreach($d->tag as $tag)
                        {
                            if($tag->type == \SwiftTag::OT_GRN)
                            {
                                $tagfound = true;
                                break;
                            }
                        }
                    }
                    if($tagfound)
                    {
                        $order->load('reception');
                        if(count($order->reception))
                        {
                            foreach($order->reception as $r)
                            {
                                if($r->grn != "")
                                {
                                    return true;                                    
                                }
                            }
                        }
                        break;
                    }
                }
                
                return $tagfound;
            }
        }        
    }
    
    public static function otCosting($nodeActivity)
    {
        $order = $nodeActivity->workflowActivity()->first()->workflowable()->first();
        if(count($order))
        {
            //Check if bill of lading has been uploaded
            //If it is, Order is now in transit
            $docs = $order->document()->get();
            if(count($docs))
            {
                $docs = $docs->load('tag');
                foreach($docs as $d)
                {
                    $tagfound = false;
                    //Get Tags
                    if(count($d->tag))
                    {
                        foreach($d->tag as $tag)
                        {
                            if($tag->type == \SwiftTag::OT_COSTING)
                            {
                                $tagfound = true;
                                break;
                            }
                        }
                    }
                    if($tagfound)
                    {
                        break;
                    }
                }
                
                return $tagfound;
            }
        }
        
        return false;        
    } 
    
    public static function otEnd($nodeActivity)
    {
        return true;
    }
    
}