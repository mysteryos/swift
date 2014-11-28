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
Use \Es;

class OrderTrackingHelper{
    
    public $message;
    public $chclIndex = 'chcl';
    public $chclType = 'storage';
    
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
                                                   "So, we need to talk. Someone has already filled in some customs information, without a <b>Notice of Arrival</b> document present on this form");
                            $this->message[] = array('type'=>'info','message'=>'');
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
                                                    $billofladingnumbermissing = array("A recent discovery showed that you have a <b>Bill of Lading</b> document present, but no Bill of Lading number filled in, in your freight section.",
                                                                                       "Your <b>Bill of Lading</b> number is missing. Please fill it in.",
                                                                                        );
                                                    $this->message[] = array('type'=>'warning','message'=>'');
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
    
    //When ETA is not matching date of upload of NOA
    private function incorrectdata()
    {
        
    }
    
    /*
     * Goes through elasticsearch
     */
    public function searchCHCLVessel($vessel,$voyage)
    {
        $params = array();
        $params['type'] = $this->chclType;
        $params['index'] = $this->chclIndex;
        $params['body']=array('query'=>array(
                                    'fuzzy_like_this_field'=>array(
                                            "vessel" => array('like_text'=>$vessel)
                                        ),
                                    'fuzzy_like_this_field'=>array(
                                            "voy" => array('like_text'=>$voyage)
                                        )
                                    )
                                );
        return Es::search($params);
    }
    
    /*
     * Elastic Search Update Data
     */
    public function esUpdate($job,$data)
    {
        $params = array();
        $params['index'] = \App::environment();
        $params['type'] = $data['context'];
        $order = SwiftOrder::find($data['order_id']);
        if($order)
        {
            $params['id'] = $order->id;
            $params['timestamp'] = $order->updated_at->toDateTimeString();
            switch($data['info-context'])
            {
                case 'order':
                case 'purchaseOrder':
                case 'reception':
                case 'freight':
                case 'shipment':
                case 'customsDeclaration':
                    $relation = $order->{$data['info-context']}()->get();
                    if(count($relation))
                    {
                        $params['doc'][$data['info-context']] = $relation->toArray();
                    }
                    else
                    {
                        $params['doc'][$data['info-context']] = array();
                    }
                    break;
                default:
                    $job->delete();
                    return;
                    break;
            }
            Es::update($params);
            $job->delete();
        }
    }
    
    /*
     * Elastic Search Index Data (For first Time)
     */
    public function esIndex($job,$data)
    {
        $params = array();
        $params['index'] = \App::environment();
        $params['type'] = $data['context'];
        $order = SwiftOrder::find($data['order_id']);
        if($order)
        {
            $params['id'] = $order->id;
            $params['timestamp'] = $order->updated_at->toDateTimeString();
            $params['order'] = $order->toArray();
            Es::index($params);
            $job->delete();
        }
    }
    
}