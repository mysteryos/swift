<?php

Namespace Swift\Services;

Use \SwiftNodeActivity;
Use \SwiftNodePermission;
Use \SwiftNodeDefinition;
Use \SwiftWorkflowActivity;
Use \SwiftWorkflowType;
Use \SwiftOrder;
Use \SwiftDocs;
Use \SwiftTag;
Use \SwiftFreight;

class OrderTracking{
    
    public $message;
    
    public function smartMessage($data)
    {
        //Early Node
        $this->earlyNode($data);
        
    }
    
    /*
     * Checks for cases where data has been input before proper step has been reached
     */
    private function earlyNode($data)
    {
        if($data['current_activity']['status'] == SwiftWorkflowActivity::INPROGRESS && isset($data['current_acivity']['definition_obj']))
        {
            //Workflow is in progress, let's analyse
            foreach($data['current_acivity']['definition_obj'] as $d)
            {
                switch($d->name)
                {
                    case 'ot_preparation':
                    case 'ot_transit':
                        if(count($data['order']->customsDeclaration))
                        {
                            $custommessage = array("We have noticed that you have already filled in customs information but <b>Notice of Arrival</b> document is still missing.",
                                                   "Sometimes, a <b>Notice of Arrival</b> document will help change the status from 'In transit' to 'Custom Declaration'",
                                                   "So, we need to talk. Someone has already filled in some customs information, without a <b>Notice of Arrival</b> document present on this form");
                            $this->$message[] = array('type'=>'info','message'=>'');
                        }
                        break;
                    
                }
            }
        }
    }
    
    private function validateNode($data)
    {
        if($data['current_activity']['status'] == SwiftWorkflowActivity::INPROGRESS && isset($data['current_acivity']['definition_obj']))
        {
            //Workflow is in progress, let's analyse
            foreach($data['current_acivity']['definition_obj'] as $d)
            {
                switch($d->name)
                {
                    case 'ot_transit':
                        if(count($data['order']->freight))
                        {
                            foreach($data['order']->freight as $f)
                            {
                                //Freight is Sea/Air and Bill of Lading is empty and has documents
                                if(in_array($f->freight_type,array(SwiftFreight::TYPE_SEA,SwiftFreight::TYPE_AIR)) && $f->bol_no == "" && count($data['order']->document))
                                {
                                    //Loop through docs
                                    foreach($data['order']->document as $doc)
                                    {
                                        //if doc has tag
                                        if(count($doc->tag))
                                        {
                                            //loop through tags
                                            foreach($doc->tag as $t)
                                            {
                                                //Bill of Lading document found
                                                if($t->type == SwiftTag::OT_BILL_OF_LADING)
                                                {
                                                    $billofladingnumbermissing = array("A recent discovery showed that you have a <b>Bill of Lading</b> document present, but no Bill of Lading number filled in.",
                                                                                       "Your <b>Bill of Lading</b> number is missing. Please fill it in.",
                                                                                        );
                                                    $this->$message[] = array('type'=>'warning','message'=>'');
                                                }
                                            }
                                        }
                                    }
                                }
                            }
                        }
                        break;
                }   
            }
        }
    }
}