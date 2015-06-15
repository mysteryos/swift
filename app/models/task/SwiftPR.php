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
        parent::__construct($controller);

        $this->list = $this->data['taskList'] = [
            [
                'name' => 'Approval',
                'permission' =>[$controller->isRetailMan],
                'type' => 'approval',
                'function' => $this->approvalTask(),
                'view' => 'product-returns/tasker-approval',
                'js' => 'pr_approval',
                'urljs' => '/js/swift/swift.pr_approval.js',
                'channel' => 'pr_approval'
            ],
            [
                'name' => 'Customer Care',
                'permission' => [$controller->isCcare],
                'type' => 'customer-care',
                'function' => 'customerCareTask',
                'view' => 'product-returns/tasker-customercare',
                'js' => 'pr_customercare',
                'urljs' => '/js/swift/swift.pr_customercare.js',
                'channel' => 'pr_customercare'
            ],
            [
                'name' => 'Store Pickup',
                'permission' => [$controller->isStorePickup],
                'type' => 'store-pickup',
                'function' => 'storePickupTask',
                'view' => 'product-returns/tasker-store_pickup',
                'js' => 'pr_store_pickup',
                'urljs' => '/js/swift/swift.pr_store_pickup.js',
                'channel' => 'pr_store_pickup'
            ],
            [
                'name' => 'Store Reception',
                'permission' => [$controller->isStoreReception],
                'type' => 'store-reception',
                'function' => 'storeReceptionTask',
                'view' => 'product-returns/tasker-store_reception',
                'js' => 'pr_store_reception',
                'urljs' => '/js/swift/swift.pr_store_reception.js',
                'channel' => 'pr_store_reception'
            ],
            [
                'name' => 'Store Validation',
                'permission' => [$controller->isStoreValidation],
                'type' => 'store-validation',
                'function' => 'storeValidationTask',
                'view' => 'product-returns/tasker-store_validation',
                'js' => 'pr_store_validation',
                'urljs' => '/js/swift/swift.pr_store_validation.js',
                'channel' => 'pr_store_validation'
            ],
            [
                'name' => 'Credit Note',
                'permission' => [$controller->isCreditor],
                'type' => 'credit-note',
                'function' => 'creditNoteTask',
                'view' => 'product-returns/tasker-credit_note',
                'js' => 'pr_credit_note',
                'urljs' => '/js/swift/swift.pr_credit_note.js',
                'channel' => 'pr_credit_note'
            ],
        ];
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
                $this->data['task'] = $this->list[0];
                return $this->approvalTask();
            }
            else
            {
                foreach($this->list as $l)
                {
                    if($type===$l['type'])
                    {
                        $this->data['task'] = $l;
                        return $this->$l['function']();
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
                        return $this->$l['function']();
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
                        return $this->$l['function']();
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
                                                            $q->where('permission_type','=',\SwiftNodePermission::RESPONSIBLE,'AND');
                                                            if($this->controller->currentUser->isSuperUser())
                                                            {
                                                                $q->whereIn('permission_name',['pr-approval-others','pr-approval-key-account','pr-approval-hospitality','pr-approval-van']);
                                                            }
                                                            else
                                                            {
                                                                $q->whereIn('permission_name',(array)array_keys($this->controller->currentUser->getMergedPermissions()));
                                                            }
                                                            return $q;
                                                        });
                                                    });
                                    })
                                    ->get();

        $this->data['edit'] = true;
        $this->data['erporder_status'] = json_encode(\Helper::jsonobject_encode(\SwiftErpOrder::$status));
        $this->data['erporder_type'] = json_encode(\Helper::jsonobject_encode(\SwiftErpOrder::$prType));

        $this->data['hasForms'] = (boolean)count($this->data['forms']);
        
        $this->sortForms();

        return $this->controller->makeView('product-returns.tasker',$this->data);
    }

    /*
     * Customer Care Task: All Categories
     *
     * @return \Illuminate\Support\Facades\View
     */
    private function customerCareTask()
    {
        $this->data['pageTitle'] = "Customer Care";

        $this->data['forms'] = $this->query
                                    ->orderBy('updated_at','desc')
                                    ->with(['order','workflow','workflow.nodes','product'=>function($q){
                                            return $q->orderBy('id','DESC')->whereHas('approvalretailman',function($q){
                                                return $q->where('approved','=',\SwiftApproval::APPROVED);
                                            });
                                        },'product.approvalretailman'])->whereHas('workflow',function($q){
                                            return $q->where('status','=',\SwiftWorkflowActivity::INPROGRESS,'AND')
                                                    ->whereHas('nodes',function($q){
                                                        return $q->where('user_id','=',0)->whereHas('permission',function($q){
                                                            $q->where('permission_type','=',\SwiftNodePermission::RESPONSIBLE,'AND');
                                                            if($this->controller->currentUser->isSuperUser())
                                                            {
                                                                $q->whereIn('permission_name',['pr-ccare-gm',
                                                                                                'pr-ccare-ht',
                                                                                                'pr-ccare-ho',
                                                                                                'pr-ccare-hh',
                                                                                                'pr-ccare-vs',
                                                                                                'pr-ccare-co',
                                                                                                'pr-ccare-s3',
                                                                                                'pr-ccare-sp',
                                                                                                'pr-ccare-s2',
                                                                                                'pr-ccare-hu',
                                                                                                'pr-ccare-he',
                                                                                                'pr-ccare-ws']);
                                                            }
                                                            else
                                                            {
                                                                $q->whereIn('permission_name',(array)array_keys($this->controller->currentUser->getMergedPermissions()));
                                                            }
                                                            return $q;
                                                        });
                                                    });
                                    })
                                    ->get();

        $this->data['hasForms'] = (boolean)count($this->data['forms']);

        $this->sortForms();

        return $this->controller->makeView('product-returns.tasker',$this->data);
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
                unset($this->data['forms'][$key]);
            }

            if($f->created_at->diffInDays(\Carbon::now()) === 1)
            {
                $this->data['yesterday_forms'][] = $f;
                unset($this->data['forms'][$key]);
            }
        }
    }
}

