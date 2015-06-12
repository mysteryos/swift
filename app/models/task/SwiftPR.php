<?php
/*
 * Name: Swift Pr Task
 * Description: Product Return Tasks - Returns views
 */

namespace Task;

class SwiftPR extends Task{
    protected $resource = "SwiftPR";

    protected $list;

    public function __construct($controller)
    {
        $this->list = $this->data['taskList'] = [
            [
                'name' => 'Approval',
                'permission' =>[$this->controller->isRetailMan],
                'type' => 'approval',
                'function' => $this->approvalTask(),
                'view' => 'tasker.approval',
                'js' => 'pr_approval',
                'urljs' => '/js/swift/swift.pr_approval.js',
                'channel' => 'pr_approval'
            ],
            [
                'name' => 'Customer Care',
                'permission' => [$this->controller->isCcare],
                'type' => 'customer-care',
                'function' => $this->customerCareTask(),
                'view' => 'tasker.customercare',
                'js' => 'pr_customercare',
                'urljs' => '/js/swift/swift.pr_customercare.js',
                'channel' => 'pr_customercare'
            ],
            [
                'name' => 'Store Pickup',
                'permission' => [$this->controller->isStorePickup],
                'type' => 'store-pickup',
                'function' => $this->storePickupTask(),
                'view' => 'tasker.store_pickup',
                'js' => 'pr_store_pickup',
                'urljs' => '/js/swift/swift.pr_store_pickup.js',
                'channel' => 'pr_store_pickup'
            ],
            [
                'name' => 'Store Reception',
                'permission' => [$this->controller->isStoreReception],
                'type' => 'store-reception',
                'function' => $this->storeReceptionTask(),
                'view' => 'tasker.store_reception',
                'js' => 'pr_store_reception',
                'urljs' => '/js/swift/swift.pr_store_reception.js',
                'channel' => 'pr_store_reception'
            ],
            [
                'name' => 'Store Validation',
                'permission' => [$this->controller->isStoreValidation],
                'type' => 'store-validation',
                'function' => $this->storeValidationTask(),
                'view' => 'tasker.store_validation',
                'js' => 'pr_store_validation',
                'urljs' => '/js/swift/swift.pr_store_validation.js',
                'channel' => 'pr_store_validation'
            ],
            [
                'name' => 'Credit Note',
                'permission' => [$this->controller->isCreditor],
                'type' => 'credit-note',
                'function' => $this->creditNoteTask(),
                'function' => 'tasker.credit_note',
                'js' => 'pr_credit_note',
                'urljs' => '/js/swift/swift.pr_credit_note.js',
                'channel' => 'pr_credit_note'
            ],
        ];

        parent::__construct($controller);
    }

    /*
     * Routes request based on user permissions and type
     */
    public function tasker($type='all')
    {
        //If super user & doesn't know where to go.
        if($this->controller->currentUser->isSuperUser())
        {
            if($type==='all')
            {
                $this->data['task'] = $this->list[2];
                return $this->approvalTask();
            }
            else
            {
                foreach($this->list as $l)
                {
                    if($type===$l['type'])
                    {
                        $this->data['task'] = $l;
                        return $l['function'];
                    }
                }
            }
        }
        else
        {
            //Normal User
            //No Type
            if($type === 'all')
            {
                foreach($this->list as $l)
                {
                    //User has permission for this type
                    if(in_array(true,$l->permission))
                    {
                        $this->data['task'] = $l;
                        return $l['function'];
                    }
                }
            }
            else
            {
                foreach($this->list as $l)
                {
                    if(in_array(true,$l->permission) && $type===$l['type'])
                    {
                        $this->data['task'] = $l;
                        return $l['function'];
                    }
                }
            }
        }
        return $this->controller->forbidden();
        
    }

    /*
     * Approvals of Retail Managers
     *
     * @return \Illuminate\Support\Facades\View
     */
    private function approvalTask()
    {
        $this->data['pageTitle'] = "Approval";

        $this->data['forms'] = $this->query
                                    ->orderBy('updated_at','desc')
                                    ->with(['workflow','workflow.nodes','product'=>function($q){
                                            return $q->orderBy('id','DESC');
                                        },'product.approvalretailman'])->whereHas('workflow',function($q){
                                            return $q->where('status','=',\SwiftWorkflowActivity::INPROGRESS,'AND')
                                                    ->whereHas('nodes',function($q){
                                                         return $q->where('user_id','=',0)->whereHas('permission',function($q){
                                                             return $q->where('permission_type','=',\SwiftNodePermission::RESPONSIBLE,'AND')
                                                                    ->whereIn('permission_name',(array)array_keys($this->controller->currentUser->getMergedPermissions()));
                                                        });
                                                    });
                                    })
                                    ->get();

        $this->data['hasForms'] = (boolean)count($this->data['forms']);
        
        $this->sortForms();

        return $this->controller->makeView('product-returns.tasker',$this->data);
    }

    private function customerCareTask()
    {
        
    }

    private function storePickupTask()
    {

    }

    private function storeReceptionTask()
    {

    }

    private function storeValidationTask()
    {
        
    }

    private function creditNoteTask()
    {
        
    }

    private function sortForms()
    {
        $this->data['today_forms'] = $this->data['yesterday_forms'] = array();

        foreach($this->data['forms'] as $key => &$f)
        {
            $f->encrypted_id = \Crypt::encrypt($f->id);

            if($f->created_at->diffInDays(\Carbon::now()) === 0)
            {
                $this->data['today_forms'][] = $f;
                unset($forms[$key]);
            }

            if($f->created_at->diffInDays(\Carbon::now()) === 1)
            {
                $this->data['yesterday_forms'][] = $f;
                unset($forms[$key]);
            }
        }
    }
}

