<?php
namespace Swift\PR;

/**
 * Description of NodeDefinitionJoin
 *
 * @author kpudaruth
 */
class NodeDefinitionJoin
{

    /*
     * Calls to undefined static functions, routes to here
     *
     * @param string $method
     * @param array $args
     *
     * @return string|boolean
     */
    public static function __callStatic($method, $args)
    {
        /*
         * To Retail Manager Routing
         */
        if(strpos($method,'aroutingTo') !== false)
        {
            return self::aRouting($args[0], str_replace('aroutingTo','',$method));
        }

        if(strpos($method,'ToCcarerouting') !== false)
        {
            return self::toCcarerouting($args[0]);
        }

        if(strpos($method,'ccareroutingTo') !== false)
        {
            return self::ccareroutingTo($args[0],str_replace('ccareroutingToCcare','',$method));
        }

        if(strpos($method,'ToPickup') !== false)
        {
            return self::toPickup($args[0]);
        }
    }

    /*
     * Start to Preparation
     *
     * @param: \Swift\Services\NodeActivity $nodeActivity
     * @return boolean
     */

    public static function startToPrep($nodeActivity)
    {
        return true;
    }


    /*
     * Preparation to Approval Routing
     *
     * @param: \Swift\Services\NodeActivity $nodeActivity
     * @return boolean
     */
    public static function prepToApprovalRouting($nodeActivity)
    {
        return true;
    }

    /*
     * Approval Routing
     * @param: \Swift\Services\NodeActivity $nodeActivity
     * @param: string $category
     *
     * @return string|boolean
     */
    public static function aRouting($nodeActivity, $category)
    {
        $pr = $nodeActivity->workflowActivity()->first()->workflowable()->first();

        if($pr)
        {
            /*
             * on delivery, invoice cancelled to system
             */
            if(in_array($pr->type,[\SwiftPR::ON_DELIVERY,\SwiftPR::INVOICE_CANCELLED]))
            {
                return strtolower($category) === "asystem";
            }

            /*
             * Salesman Type
             */
            $customer = $pr->customer()->first();
            if($customer)
            {
                switch($customer->AC09)
                {
                    //Key-Account
                    case "GM":
                        return strtolower($category) === "akeyaccount";
                        break;
                    //Hospitality
                    case "S3":
                    case "S2":
                    case "SP":
                    case "HU":
                    case "HE":
                    case "CO":
                    case "WS":
                        return strtolower($category) === "ahospitality";
                        break;
                    //Van Selling
                    case "VS":
                        return strtolower($category) === "avan";
                        break;
                    default:
                        return strtolower($category) === "aothers";
                        break;
                }
            }
        }

        return false; 
    }

    /*
     * To Customer Care Routing
     *
     * @param \Swift\Services\NodeActivity $nodeActivity
     * @return boolean
     */
    public static function toCcarerouting($nodeActivity)
    {
        //Check if all products has been cancelled
        $workflow = $nodeActivity->workflowActivity()->first();
        if($workflowActivity)
        {
            $pr = $workflow->workflowable()->first();

            $productcount = $pr->product()->count();
            $rejectedproductcount = $pr->product()->whereHas('approval',function($q){
                return $q->rejectedBy(\SwiftApproval::PR_RETAILMAN);
            })->count();

            if($productcount == $rejectedproductcount)
            {
                //All products have been rejected
                //Update Workflow as Rejected
                $workflow->status = SwiftWorkflowActivity::REJECTED;
                $workflow->save();
                NodeMail::sendCancelledMail($pr);
                return false;
            }
        }
        else
        {
            return false;
        }

        return true;
    }

    /*
     * Customer Care Routing To Customer Care Categories
     *
     * @param \Swift\Services\NodeActivity $nodeActivity
     * @param string $customerCategory
     *
     * @return boolean
     */
    public static function ccareRoutingTo($nodeActivity,$customerCategory)
    {
        $workflowActivity = $nodeActivity->workflowActivity()->first();
        if($workflowActivity)
        {
            $pr = $workflowActivity->workflowable()->first();

            if($pr)
            {

                $customer = $pr->customer()->first();
                if($customer)
                {
                    if($customer && strtolower($customer->AC09) === $customerCategory)
                    {
                        return true;
                    }

                    if(strtolower($customerCategory) === "others")
                    {
                        //Check if definition node for customer category exists
                        $nodeDefinitionCount = \SwiftNodeDefinition::where('workflow_type_id','=',$workflowActivity->workflow_type_id)
                                                ->where('name','=','ccarerouting_to_ccare'.strtolower($customer->AC09),'AND')
                                                ->count();
                        if($nodeDefinitionCount === 0)
                        {
                            return true;
                        }
                    }
                }
            }
        }

        return false;
    }

    /*
     * To Pickup Routing
     *
     * @param \Swift\Services\NodeActivity $nodeActivity
     * @return boolean
     */
    public static function toPickup($nodeActivity)
    {
        return true;
    }


    /*
     * Pickup To Reception
     *
     * @param \Swift\Services\NodeActivity $nodeActivity
     * @return boolean
     */
    public static function pickupToReception($nodeActivity)
    {
        return true;
    }

    /*
     * Reception To Store Validation
     *
     * @param \Swift\Services\NodeActivity $nodeActivity
     * @return boolean
     */
    public static function receptionToStoreValidation($nodeActivity)
    {
        return true;
    }

    /*
     * Store validation to credit note
     *
     * @param \Swift\Services\NodeActivity $nodeActivity
     * @return boolean
     */
    public static function storeValidationToCreditNote($nodeActivity)
    {
        return true;
    }

    /*
     * Credit Note To End
     *
     * @param \Swift\Services\NodeActivity $nodeActivity
     * @return boolean
     */
    public static function creditNoteToEnd($nodeActivity)
    {
        return true;
    }
    
}