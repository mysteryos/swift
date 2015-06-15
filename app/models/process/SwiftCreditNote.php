<?php
namespace Process;

/**
 * Description of SwiftCreditNote
 *
 * @author kpudaruth
 */
class SwiftCreditNote extends Process
{

    protected $resourceName = "SwiftCreditNote";

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
        return $this->processCreateByParent('creditnote',true);
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
                case 'number':
                    if(!is_numeric(\Input::get('value')))
                    {
                        return \Response::make('Please enter a valid credit note number',400);
                    }
                    break;
                default:
                    return \Response::make("Unknown field",500);
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