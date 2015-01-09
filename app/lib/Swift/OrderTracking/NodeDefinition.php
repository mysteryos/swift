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
    
    public static function otPreparation($nodeActivity,$returnReason=false)
    {
        if($returnReason)
        {
            $returnReasonList = array();
        }
        
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
                    elseif($returnReason)
                    {
                        $returnReasonList['po'] = "Create a Purchase Order entry";
                    }
                }
                elseif($returnReason)
                {
                    $returnReasonList['bol'] = "Upload and tag 'Bill of Lading' document";
                }
            }
        }

        return $returnReason ? $returnReasonList : false;
    }
    
    public static function otTransit($nodeActivity,$returnReason=false)
    {
        if($returnReason)
        {
            $returnReasonList = array();
        }
        
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
                        elseif($returnReason)
                        {
                            $returnReasonList['transit_freight'] = "Create a Freight entry";
                        }
                        break;
                    }
                    elseif($returnReason)
                    {
                        $returnReasonList['transit_noa'] = "Upload and tag 'Notice of Arrival' document";
                    }
                }
            }
        }
        
        return $returnReason ? $returnReasonList : false;
    }
    
    public static function otCustoms($nodeActivity,$returnReason=false)
    {
        if($returnReason)
        {
            $returnReasonList = array();
        }        
        
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
                                if($c->customs_reference != "")
                                {
                                    if($c->customs_status == \SwiftCustomsDeclaration::CLEARED)
                                    {
                                        return true;
                                    }
                                    elseif($returnReason)
                                    {
                                        $returnReasonList['customs_cleared'] = "Set Customs Declaration Status to 'cleared'";
                                    }
                                }
                                elseif($returnReason)
                                {
                                    $returnReasonList['customs_ref'] = "Set Customs Declaration Reference";
                                }
                            }
                        }
                        elseif($returnReason)
                        {
                            $returnReasonList['customs'] = "Create new Customs Declaration entry";
                        }
                        break;
                    }
                    elseif($returnReason)
                    {
                        $returnReasonList['customs_boe'] = "Upload and tag 'Bill of Entry' document";
                    }
                }
            }
        }
        
        return $returnReason ? $returnReasonList : false;         
    }
    
    public static function otPickup($nodeActivity,$returnReason=false)
    {
        if($returnReason)
        {
            $returnReasonList = array();
        }        
        
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
                    if($f->freight_type == \SwiftFreight::TYPE_LAND)
                    {
                        if($f->freight_etd != "")
                        {
                            return true;
                        }
                        elseif($returnReason)
                        {
                            $returnReasonList['freight_etd'] = "Set Freight ETD for freight ID: ".$f->id;
                        }
                    }
                                
                    /*
                     * Check if incoterm is DAT/DAP, local pickup will arranged by foreign company
                     */
                    if((int)$f->incoterm !== 0)
                    {
                        if(in_array($f->incoterm,array(\SwiftFreight::INCOTERM_DAT,\SwiftFreight::INCOTERM_DAP)))
                        {
                            return true;
                        }
                        elseif($returnReason)
                        {
                            $returnReasonList['freight_pickup'] = "Set Local pickup since incoterm is '".$f->incotermstext."'";
                        }
                    }
                    elseif($returnReason)
                    {
                        $returnReasonList['freight_incoterms'] = "Set Incoterms";
                    }
                }
            }
        }       
        
        return $returnReason ? $returnReasonList : false;       
    }
    
    public static function otReception($nodeActivity,$returnReason=false)
    {
        if($returnReason)
        {
            $returnReasonList = array();
        }        
        
        $order = $nodeActivity->workflowActivity()->first()->workflowable()->first();
        if(count($order))
        {
            //Check if bill of lading has been uploaded
            //If it is, Order is now in transit
            /*$docs = $order->document()->get();
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
            }*/
            $order->load('reception');
            if(count($order->reception))
            {
                foreach($order->reception as $r)
                {
                    if($r->grn != "")
                    {
                        return true;                                    
                    }
                    elseif($returnReason)
                    {
                        $returnReasonList['reception_grn'] = "Set GRN number";
                    }                    
                }
            }
            elseif($returnReason)
            {
                $returnReasonList['reception_entry'] = "Create a Reception entry";
            }            
        }
        
        return $returnReason ? $returnReasonList : false;
    }
    
    public static function otCosting($nodeActivity,$returnReason=false)
    {
        if($returnReason)
        {
            $returnReasonList = array();
        }        
        
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
                
                if($tagfound)
                {
                    return $tagfound;
                }
                elseif($returnReason)
                {
                    $returnReasonList['costing_tag'] = "Upload and tag 'Costing' document";
                }
            }
            elseif($returnReason)
            {
                $returnReasonList['costing_tag'] = "Upload and tag 'Costing' document";
            }
        }
        
        return $returnReason ? $returnReasonList : false;       
    } 
    
    public static function otEnd($nodeActivity)
    {
        return true;
    }
    
}