<?php
/**
 * Description of NodeDefinition
 *
 * @author kpudaruth
 */

namespace Swift\PR;

class NodeDefinition
{

    public static function __callStatic($method, $args) {
        /*
         * Manager Nodes
         */

        if(strpos($method,'prApproval') !== false)
        {
            return self::prApproval($args[0],str_replace('prApproval','',$method),$args[1]);
        }

        /*
         * Customer Care
         */

        if(strpos($method,'prCustomercare') !== false)
        {
            return self::prCustomercare($args[0],$args[1]);
        }
    }

    public static function prStart($nodeActivity)
    {
        return true;
    }

    public static function prPreparation($nodeActivity,$returnReason=false)
    {
        if($returnReason)
        {
            $returnReasonList = array();
        }

        $pr = $nodeActivity->workflowActivity()->first()->workflowable()->first();

        if(count($pr))
        {
            if($pr->type === \SwiftPR::ON_DELIVERY)
            {
                if($pr->paper_number === "")
                {
                    $returnReasonList['norfrfnumber'] = "Please input an RFRF number";
                    return $returnReason ? $returnReasonList : false;
                }
            }

            $countapproval = $pr->approval()->where('type','=',\SwiftApproval::PR_REQUESTER)->count();
            if($countapproval > 0)
            {
                return true;
            }
            else
            {
                $returnReasonList['publish'] = "Publish Form";
            }
        }
        
        return $returnReason ? $returnReasonList : false;
    }

    public static function prApproval($nodeActivity,$type,$returnReason=false)
    {

        if($returnReason)
        {
            $returnReasonList = array();
        }

        switch(strtolower($type))
        {
            case 'routing':
                return true;
                break;
            case 'system':
                if($returnReason)
                {
                    return ['weworking'=>'System is currently approving the products'];
                }
                
                if(\Helper::loginSysUser())
                {
                    $pr = $nodeActivity->workflowActivity()->first()->workflowable()->first();
                    $pr->load('product');

                    foreach($pr->product as $p)
                    {
                        $p->approval()->save(new \SwiftApproval([
                            'type' => \SwiftApproval::PR_RETAILMAN,
                            'approved' => \SwiftApproval::APPROVED,
                            'approval_user_id' => \Sentry::getUser()->id
                        ]));
                    }

                    return true;
                }
                break;
            default:
                $pr = $nodeActivity->workflowActivity()->first()->workflowable()->first();
                if($pr)
                {
                    $countProducts = $pr->product()->count();
                    $countApprovals = $pr->product()->whereHas('approval',function($q){
                        return $q->approvedBy(\SwiftApproval::PR_RETAILMAN);
                    })->count();

                    if($countProducts === $countApprovals)
                    {
                        return true;
                    }
                    else
                    {
                        $returnReasonList['approval'] = "Please approve all products";
                    }
                }
                break;
        }
        
        return $returnReason ? $returnReasonList : false;
    }

    public static function prCustomercare($nodeActivity,$customerType,$returnReason=false)
    {
        if($returnReason)
        {
            $returnReasonList = array();
        }
        
        switch(strttolower($customerType))
        {
            case 'routing':
                return true;
                break;
            default:
                $pr = $nodeActivity->workflowActivity()->first()->workflowable()->first();
                if($pr)
                {
                    $order = $pr->order()->get();
                    if(count($order))
                    {
                        foreach($order as $o)
                        {
                            if($o->ref != "" && $o->status == \SwiftErpOrder::FILLED)
                            {
                                return true;
                            }
                        }
                    }
                    else
                    {
                        $returnReasonList['ccare'] = "Please create a JDE order & set its status to 'filled'";
                    }
                }
                break;
        }
        
        return $returnReason ? $returnReasonList : false;
    }

    public static function prPickup($nodeActivity,$returnReason=false)
    {

        if($returnReason)
        {
            $returnReasonList = array();
        }

        $pr = $nodeActivity->workflowActivity()->first()->workflowable()->first();
        if($pr)
        {
            //If type is 'on delivery', or 'invoice cancelled' = no pickup
            if(in_array($pr->type,[\SwiftPR::ON_DELIVERY,\SwiftPR::INVOICE_CANCELLED]))
            {
                return true;
            }
            
            //Else check products
            $countProducts = $pr->product()
                                ->where('pickup','=',\SwiftPRProduct::PICKUP)
                                ->whereHas('approval',function($q){
                                    return $q->approvedBy(\SwiftApproval::PR_RETAILMAN);
                                })->count();

            /*
             * Got Products to Pickup
             */
            if($countProducts > 0)
            {
                /*
                 * Check if form has been published
                 */

                $countApproval = $pr->approval()->approvedBy(\SwiftApproval::PR_PICKUP)->count();

                if($countApproval > 0)
                {
                    return true;
                }
                else
                {
                    $returnReasonList['pickup'] = "Store Pickup - Please publish the form";
                }
            }
            else
            {
                /*
                 * ByPass Step
                 */
                return true;
            }
        }

        return $returnReason ? $returnReasonList : false;
    }

    public static function prReception($nodeActivity,$returnReason=false)
    {
        if($returnReason)
        {
            $returnReasonList = array();
        }

        $pr = $nodeActivity->workflowActivity()->first()->workflowable()->first();
        if($pr)
        {
            //If type is 'on delivery', or 'invoice cancelled' = no reception

            if(in_array($pr->type,[\SwiftPR::ON_DELIVERY,\SwiftPR::INVOICE_CANCELLED]))
            {
                return true;
            }

            //Else check products

            $products = $pr->product()
                            ->with('discrepancy')
                            ->where('pickup','=',\SwiftPRProduct::PICKUP)
                            ->whereHas('approval',function($q){
                                return $q->approvedBy(\SwiftApproval::PR_RETAILMAN);
                            })->get();

            /*
             * Got Products to Pickup
             */
            if(count($products) > 0)
            {
                //verify each product;
                foreach($products as $p)
                {
                    if($p->qty_client !== $p->qty_pickup && count($p->discrepancy) === 0)
                    {
                        $returnReasonList['discrepancy'] = "Please set a reason for discrepancy on product ID: ".$p->id;
                        break;
                    }

                    if($p->qty_pickup !== ($p->qty_triage_picking + $p->qty_triage_disposal))
                    {
                        $returnReasonList['notally'] = "Qty picking & disposal doesn't tally with qty pickup for product ID: ".$p->id;
                        break;
                    }
                }

                if(count($returnReasonList) === 0)
                {
                    if($pr->approval()->approvedBy(\SwiftApproval::PR_RECEPTION)->count())
                    {
                        return true;
                    }
                    else
                    {
                        $returnReasonList['nopublish'] = "Store Reception - Please publish the form";
                    }
                }
            }
            else
            {
                /*
                 * ByPass Step - No pickup = No Reception
                 */
                return true;
            }
        }

        return $returnReason ? $returnReasonList : false;
    }

    public function prStoreValidation($nodeActivity,$returnReason=false)
    {
        if($returnReason)
        {
            $returnReasonList = array();
        }

        $pr = $nodeActivity->workflowActivity()->first()->workflowable()->first();
        if($pr)
        {
            //Else check products

            $products = $pr->product()
                            ->with('discrepancy')
                            ->whereHas('approval',function($q){
                                return $q->approvedBy(\SwiftApproval::PR_RETAILMAN);
                            })->get();

            /*
             * Got Products to Store Validation
             */
            if(count($products) > 0)
            {
                //verify each product;
                foreach($products as $p)
                {
                    if($p->qty_pickup !== $p->qty_store && count($p->discrepancy) === 0)
                    {
                        $returnReasonList['discrepancy'] = "Please set a reason for discrepancy on product ID: ".$p->id;
                        break;
                    }

                    if($p->qty_store !== ($p->qty_triage_picking + $p->qty_triage_disposal))
                    {
                        $returnReasonList['notally'] = "Qty picking & disposal doesn't tally with qty store for product ID: ".$p->id;
                        break;
                    }
                }

                if(count($returnReasonList) === 0)
                {
                    if($pr->approval()->approvedBy(\SwiftApproval::PR_STOREVALIDATION)->count())
                    {
                        return true;
                    }
                    else
                    {
                        $returnReasonList['nopublish'] = "Store Validation - Please publish the form";
                    }
                }
            }
            else
            {
                /*
                 * ByPass Step - No pickup = No Reception
                 */
                return true;
            }
        }

        return $returnReason ? $returnReasonList : false;

    }

    public function prCreditNote($nodeActivity,$returnReason=false)
    {
        if($returnReason)
        {
            $returnReasonList = array();
        }

        $pr = $nodeActivity->workflowActivity()->first()->workflowable()->first();
        if($pr)
        {
            $creditNote = $pr->creditNote()->get();

            if(count($creditNote))
            {
                foreach($creditNote as $c)
                {
                    if($c->number !== "")
                    {
                        $returnReasonList['nonumber'] = "Please add a credit note number";
                    }
                }

                if(count($returnReasonList) === 0)
                {
                    if($pr->approval()->approvedBy(\SwiftApproval::PR_CREDITNOTE)->count() > 0)
                    {
                        return true;
                    }
                    else
                    {
                        $returnReasonList['nopublish'] = "Accounting - Please publish the form";
                    }
                }
            }
            else
            {
                $returnReasonList['nocreditnote'] = "Please add a credit note";
            }
        }

        return $returnReason ? $returnReasonList : false;
    }
    
    public static function prEnd($nodeActivity)
    {
        return true;
    }
}