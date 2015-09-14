<?php
/*
 * Name: Swift Pr Task
 * Description: Product Return Tasks - Returns views
 */

namespace Task;

class SwiftPR extends Task{
    protected $table = "SwiftPR";

    protected $list;

    public function __construct($controller)
    {
        parent::__construct($controller);

        $this->list = $this->data['taskList'] = [
            [
                'name' => 'Approval',
                'permission' =>[$controller->permission->isRetailMan()],
                'type' => 'approval',
                'function' => 'approvalTask',
                'view' => 'product-returns/tasker-approval',
                'js' => 'pr_approval',
                'urljs' => '/js/swift/swift.pr_approval.js',
                'channel' => 'pr_approval'
            ],
            [
                'name' => 'Customer Care',
                'permission' => [$controller->permission->isCcare()],
                'type' => 'customer-care',
                'function' => 'customerCareTask',
                'view' => 'product-returns/tasker-customercare',
                'js' => 'pr_customercare',
                'urljs' => '/js/swift/swift.pr_customercare.js',
                'channel' => 'pr_customercare'
            ],
            [
                'name' => 'Store Pickup',
                'permission' => [$controller->permission->isStorePickup()],
                'type' => 'store-pickup',
                'function' => 'storePickupTask',
                'view' => 'product-returns/tasker-store_pickup',
                'js' => 'pr_store_pickup',
                'urljs' => '/js/swift/swift.pr_store_pickup.js',
                'channel' => 'pr_store_pickup'
            ],
            [
                'name' => 'Store Reception',
                'permission' => [$controller->permission->isStoreReception()],
                'type' => 'store-reception',
                'function' => 'storeReceptionTask',
                'view' => 'product-returns/tasker-store_reception',
                'js' => 'pr_store_reception',
                'urljs' => '/js/swift/swift.pr_store_reception.js',
                'channel' => 'pr_store_reception'
            ],
            [
                'name' => 'Store Validation',
                'permission' => [$controller->permission->isStoreValidation()],
                'type' => 'store-validation',
                'function' => 'storeValidationTask',
                'view' => 'product-returns/tasker-store_validation',
                'js' => 'pr_store_validation',
                'urljs' => '/js/swift/swift.pr_store_validation.js',
                'channel' => 'pr_store_validation'
            ],
            [
                'name' => 'Credit Note',
                'permission' => [$controller->permission->isCreditor()],
                'type' => 'credit-note',
                'function' => 'creditNoteTask',
                'view' => 'product-returns/tasker-credit_note',
                'js' => 'pr_credit_note',
                'urljs' => '/js/swift/swift.pr_credit_note.js',
                'channel' => 'pr_credit_note'
            ],
        ];
    }

    public function registerFormFilters()
    {
        \Input::flash();

        $this->controller->filter['filter_start_date']  = ['name'=>'Start Date',
                                                    'value' => \Input::get('filter_start_date'),
                                                    'enabled' => \Input::has('filter_start_date'),
                                                    'function' => 'filterStartDate'
                                                ];

        $this->controller->filter['filter_end_date']    = ['name'=>'End Date',
                                                    'value' => \Input::get('filter_end_date'),
                                                    'enabled' => \Input::has('filter_end_date'),
                                                    'function' => 'filterEndDate'
                                                ];

        $this->controller->filter['filter_customer_code'] = ['name'=>'Customer',
                                                'value' => \Input::has('filter_customer_code') ? \JdeCustomer::find(\Input::get('filter_customer_code'))->getReadableName() : false,
                                                'enabled' => \Input::has('filter_customer_code'),
                                                'function' => 'filterCustomerCode'
                                                ];

        $this->controller->filter['filter_step'] = ['name'=>'Current Step',
                                                    'value' => \Input::has('filter_step') ? \SwiftNodeDefinition::find(\Input::get('filter_step'))->label :false,
                                                    'enabled' => \Input::has('filter_step'),
                                                    'function' => 'filterStep'
                                                    ];

        $this->controller->filter['filter_owner_user_id'] = ['name' => 'Owner',
                                                    'value' => \Input::has('filter_owner_user_id') ? \Sentry::findUserById(\Input::get('filter_owner_user_id'))->first_name." ".\Sentry::findUserById(\Input::get('filter_owner_user_id'))->last_name : false,
                                                    'enabled' => \Input::has('filter_owner_user_id'),
                                                    'function' => 'filterOwnerUserId'
                                                ];

        $this->controller->filter['filter_driver_id'] = ['name' => 'Driver',
                                                'value' => \Input::has('filter_driver_id') ? \SwiftDriver::find(\Input::get('filter_driver_id'))->name : false,
                                                'enabled' => \Input::has('filter_driver_id'),
                                                'function' => 'filterDriverId'
                                            ];

        $this->controller->data['filterActive'] = (boolean)count(
                                                        array_filter($this->controller->filter,function($v){
                                                            return $v['enabled'] === true;
                                                        })
                                                    );
    }

    /*
     * Filter Functions: START
     */

    private function filterStartDate($query)
    {
        $query->where('created_at','>=',\Input::get('filter_start_date'));
    }

    private function filterEndDate($query)
    {
        $query->where('created_at','<=',\Input::get('filter_end_date'));
    }

    private function filterCustomerCode($query)
    {
        $query->where('customer_code','=',\Input::get('filter_customer_code'));
    }

    private function filterStep($query)
    {
        $query->whereHas('workflow',function($q){
            return $q->inprogress()->whereHas('pendingNodes',function($q){
                return $q->whereHas('definition',function($q){
                   return $q->where('id','=',\Input::get('filter_step'));
                });
            });
        });
    }

    private function filterOwnerUserId($query)
    {
        $query->where('owner_user_id','=',\Input::get('filter_owner_user_id'));
    }

    private function filterDriverId($query)
    {
        $query->whereHas('pickup',function($q){
            return $q->where('driver_id','=',\Input::get('driver_id'));
        });
    }

    /*
     * Filter Functions: END
     */

    /*
     * Apply Filters
     */

    public function applyFilters(&$query)
    {
        foreach($this->controller->filter as $filter_name => $filter_array)
        {
            if($filter_array['enabled'] &&
                array_key_exists('function',$filter_array) &&
                method_exists($this,$filter_array['function']))
            {
                $this->{$filter_array['function']}($query);
            }
        }
    }

    public function getListOwners()
    {
        return \User::remember(30)
                ->has('pr')
                ->orderBy('first_name','ASC')
                ->orderBy('last_name','ASC')
                ->get();
    }

    public function getListStep()
    {
        return \SwiftNodeDefinition::remember(60)
                ->whereHas('workflow',function($q){
                    return $q->where('name','=',$this->controller->context);
                })
                ->orderBy('id','ASC')
                ->get();
    }

    public function getListDrivers()
    {
        return \SwiftDriver::remember(30)
                ->whereHas('pickup',function($q){
                    return $q->where('pickable_type','SwiftPR');
                })
                ->orderBy('name','ASC')
                ->get();
    }

    public function getListCustomers()
    {
        return \JdeCustomer::remember(30)
                ->has('pr')
                ->orderBy('ALPH','ASC')
                ->get();
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

        $this->data['forms'] = $this->resource->query()
                                    ->orderBy('updated_at','desc')
                                    ->with(['product'=>function($q){
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

        $this->data['forms'] = $this->resource->query()
                                    ->orderBy('updated_at','desc')
                                    ->with(['order','product'=>function($q){
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

        $this->data['edit'] = true;
        $this->data['erporder_status'] = json_encode(\Helper::jsonobject_encode(\SwiftErpOrder::$status));
        $this->data['erporder_type'] = json_encode(\Helper::jsonobject_encode(\SwiftErpOrder::$prType));

        $this->sortForms();

        return $this->controller->makeView('product-returns.tasker',$this->data);
    }

    /*
     * Store Pickup: Salesman Workflow Only
     *
     * @return \Illuminate\Support\Facades\View
     */
    private function storePickupTask()
    {
        $this->data['pageTitle'] = "Store Pickup";

        $this->data['forms'] = $this->resource->query()
                                    ->orderBy('updated_at','desc')
                                    ->with(['pickup','product'=>function($q){
                                            return $q->orderBy('id','DESC')
                                                    ->whereHas('approvalretailman',function($q){
                                                        return $q->where('approved','=',\SwiftApproval::APPROVED);
                                                    })
                                                    ->where('pickup','=',\SwiftPRProduct::PICKUP,'AND');
                                        },'product.approvalretailman'])
                                    ->whereHas('product',function($q) {
                                        return $q->whereHas('approvalretailman',function($q){
                                                    return $q->where('approved','=',\SwiftApproval::APPROVED);
                                                })
                                                ->where('pickup','=',\SwiftPRProduct::PICKUP,'AND');
                                    })
                                    ->whereHas('workflow',function($q){
                                            return $q->where('status','=',\SwiftWorkflowActivity::INPROGRESS,'AND')
                                                    ->whereHas('nodes',function($q){
                                                        return $q->where('user_id','=',0)->whereHas('permission',function($q){
                                                            $q->where('permission_type','=',\SwiftNodePermission::RESPONSIBLE,'AND');
                                                            if($this->controller->currentUser->isSuperUser())
                                                            {
                                                                $q->whereIn('permission_name',['pr-pickup']);
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

        $this->data['edit'] = true;

        $this->data['pickup_status'] = json_encode(\Helper::jsonobject_encode(\SwiftPickup::$pr_status));
        $this->data['drivers'] = json_encode(\Helper::jsonobject_encode(\SwiftDriver::getAll()));

        $this->sortForms();

        return $this->controller->makeView('product-returns.tasker',$this->data);
    }

    /*
     * Store Reception
     *
     * @return \Illuminate\Support\Facades\View
     */
    private function storeReceptionTask()
    {
        $this->data['pageTitle'] = "Store Reception";

        $this->data['forms'] = $this->resource->query()
                                    ->orderBy('updated_at','desc')
                                    ->with(['pickup','product'=>function($q){
                                            return $q->orderBy('id','DESC')
                                                    ->whereHas('approvalretailman',function($q){
                                                        return $q->where('approved','=',\SwiftApproval::APPROVED);
                                                    })
                                                    ->where('pickup','=',\SwiftPRProduct::PICKUP,'AND');
                                        },'product.approvalretailman'])
                                    ->whereHas('product',function($q) {
                                        return $q->whereHas('approvalretailman',function($q){
                                                    return $q->where('approved','=',\SwiftApproval::APPROVED);
                                                })
                                                ->where('pickup','=',\SwiftPRProduct::PICKUP,'AND');
                                    })
                                    ->whereHas('workflow',function($q){
                                            return $q->where('status','=',\SwiftWorkflowActivity::INPROGRESS,'AND')
                                                    ->whereHas('nodes',function($q){
                                                        return $q->where('user_id','=',0)->whereHas('permission',function($q){
                                                            $q->where('permission_type','=',\SwiftNodePermission::RESPONSIBLE,'AND');
                                                            if($this->controller->currentUser->isSuperUser())
                                                            {
                                                                $q->whereIn('permission_name',['pr-reception']);
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
        $this->data['publishReception'] = true;
        $this->data['addProduct'] = false;
        $this->data['approval_code'] = json_encode(\Helper::jsonobject_encode(\SwiftApproval::$approved));
        $this->data['product_reason_codes'] = json_encode(\Helper::jsonobject_encode(\SwiftPRReason::getAll()));

        $this->data['edit'] = true;

        $this->sortForms();

        return $this->controller->makeView('product-returns.tasker',$this->data);
    }

    /*
     * Store Validation Task
     *
     * @return \Illuminate\Support\Facades\View
     */
    private function storeValidationTask()
    {
        $this->data['pageTitle'] = "Store Validation";

        $this->data['forms'] = $this->resource->query()
                                    ->orderBy('updated_at','desc')
                                    ->with(['pickup','product'=>function($q){
                                            return $q->orderBy('id','DESC')
                                                    ->whereHas('approvalretailman',function($q){
                                                        return $q->where('approved','=',\SwiftApproval::APPROVED);
                                                    })
                                                    ->where('pickup','=',\SwiftPRProduct::PICKUP,'AND');
                                        },'product.approvalretailman'])
                                    ->whereHas('product',function($q) {
                                        return $q->whereHas('approvalretailman',function($q){
                                                    return $q->where('approved','=',\SwiftApproval::APPROVED);
                                                })
                                                ->where('pickup','=',\SwiftPRProduct::PICKUP,'AND');
                                    })
                                    ->whereHas('workflow',function($q){
                                            return $q->where('status','=',\SwiftWorkflowActivity::INPROGRESS,'AND')
                                                    ->whereHas('nodes',function($q){
                                                        return $q->where('user_id','=',0)->whereHas('permission',function($q){
                                                            $q->where('permission_type','=',\SwiftNodePermission::RESPONSIBLE,'AND');
                                                            if($this->controller->currentUser->isSuperUser())
                                                            {
                                                                $q->whereIn('permission_name',['pr-store-validation']);
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
        $this->data['publishStoreValidation'] = true;
        $this->data['publishReception'] = false;
        $this->data['addProduct'] = false;
        $this->data['approval_code'] = json_encode(\Helper::jsonobject_encode(\SwiftApproval::$approved));
        $this->data['product_reason_codes'] = json_encode(\Helper::jsonobject_encode(\SwiftPRReason::getAll()));

        $this->data['edit'] = true;

        $this->sortForms();

        return $this->controller->makeView('product-returns.tasker',$this->data);
    }

    /*
     * Credit Note Issue
     *
     * @return \Illuminate\Support\Facades\View
     */
    private function creditNoteTask()
    {
        $this->data['pageTitle'] = "Credit Note";

        $this->data['forms'] = $this->resource->query()
                                    ->orderBy('updated_at','desc')
                                    ->with(['order'])
                                    ->whereHas('workflow',function($q){
                                        return $q->where('status','=',\SwiftWorkflowActivity::INPROGRESS,'AND')
                                                ->whereHas('nodes',function($q){
                                                    return $q->where('user_id','=',0)->whereHas('permission',function($q){
                                                        $q->where('permission_type','=',\SwiftNodePermission::RESPONSIBLE,'AND');
                                                        if($this->controller->currentUser->isSuperUser())
                                                        {
                                                            $q->whereIn('permission_name',['pr-credit-note']);
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

        $this->data['publishCreditNote'] = true;

        $this->data['edit'] = true;

        $this->sortForms();

        return $this->controller->makeView('product-returns.tasker',$this->data);
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

