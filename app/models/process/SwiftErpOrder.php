<?php
namespace Process;

/**
 * Description of SwiftErpOrder
 *
 * @author kpudaruth
 */
class SwiftErpOrder extends Process
{
    
    protected $resourceName = "SwiftErpOrder";

    public function __construct($controller)
    {
        parent::__construct($controller);
    }

    /*
     * Create Resource
     * 
     * @return \Illuminate\Support\Facades\Response
     */
    public function create()
    {
        //Swift AP Request Access
        if($this->parentResourceName === "SwiftAPRequest")
        {
            if(!$this->controller->currentUser->hasAccess($this->controller->adminPermission))
            {
                $this->resource->type = \SwiftErpOrder::TYPE_ORDER_AP;
            }
        }

        return $this->processCreateByParent('order',true);
    }

    /*
     * Save By Parent Resource
     *
     * @param string $resourceId
     * @param string|boolean $parentResourceName
     * @return \Illuminate\Support\Facades\Response
     */
    public function saveByParent($parentResourceId,$parentResourceName=false)
    {
        $this->setParentForm(\Crypt::decrypt($parentResourceId));

        /*
         * Manual Validation
         */
        if($this->parentForm)
        {
            switch(\Input::get('name'))
            {
                case 'status':
                    if(!array_key_exists(\Input::get('value'),\SwiftErpOrder::$status))
                    {
                        return \Response::make('Please select a valid status',400);
                    }
                    break;
                case 'type':
                    if(!array_key_exists(\Input::get('value'),\SwiftErpOrder::$type))
                    {
                        return \Response::make('Please select a valid type',400);
                    }
                    break;
            }

            /*
             * New Erp Order
             */
            if(is_numeric(\Input::get('pk')))
            {
                return $this->create();
            }
            else
            {
                //Swift AP Request Access
                if(!$this->controller->currentUser->hasAccess($this->controller->adminPermission) &&
                    \Input::get('name') == 'type' &&
                    $this->parentResourceName === "SwiftAPRequest")
                {
                    return \Response::make("You don't have permission to modify type of order.",400);
                }

                return $this->processPut(true);
            }
        }
        else
        {
            return \Response::make('Form not found',404);
        }
    }

    /*
     * Delete Resource
     * 
     * @param boolean $workflowUpdate
     * @return \Illuminate\Support\Facades\Response
     */
    public function delete($workflowUpdate = false)
    {
        return $this->processDelete($workflowUpdate);
    }
}