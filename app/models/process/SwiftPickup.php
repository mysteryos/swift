<?php
/*
 * Name: Swift Pickup
 * Description: Processes Pickup
 */

namespace Process;

class SwiftPickup extends Process
{
    protected $resourceName = "SwiftPickup";

    public function __construct($controller)
    {
        parent::__construct($controller);
    }

    public function create()
    {
        return $this->processCreateByParent('pickup',true);
    }

/*
     * Save By Parent Resource
     *
     * @param string $resourceId
     * @param string|boolean $parentResourceName
     * @return \Illuminate\Support\Facades\Response
     */
    public function saveByParent($parentResourceId)
    {
        $this->parentForm = $this->parentResource->find($parentResourceId);

        /*
         * Manual Validation
         */
        if($this->parentForm)
        {
            switch(\Input::get('name'))
            {
                case 'status':
                    if(!array_key_exists(\Input::get('value'),\SwiftPickup::$status))
                    {
                        return \Response::make('Please select a valid status',400);
                    }
                    break;
                case 'pickup_date':
                    try
                    {
                        $mydate = \Carbon::createFromFormat("Y-m-d",\Input::get('value'));
                    } catch (Exception $ex) {
                        return \Response::make('Please enter a valid date',400);
                    }
                    break;
                case 'driver_id':
                    if(is_numeric(\Input::get('value')))
                    {
                        if(!\SwiftDriver::find(\Input::get('value')))
                        {
                            return \Response::make("Please select a valid driver",400);
                        }
                    }
                    else
                    {
                        return \Response::make("Please select a valid driver",400);
                    }
                    break;
                case 'comment':
                    break;
                default:
                    return \Response::make('Invalid field',400);
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
                return $this->processPut();
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

