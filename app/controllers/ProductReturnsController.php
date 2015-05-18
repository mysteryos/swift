<?php

class ProductReturnsController extends UserController {
    
    public function __construct(){
        parent::__construct();
        $this->pageName = "Product Returns";
        $this->rootURL = $this->data['rootURL'] = $this->context = "product-returns";
        $this->adminPermission = \Config::get("permission.{$this->context}.admin");
        $this->viewPermission = \Config::get("permission.{$this->context}.view");
        $this->editPermission = \Config::get("permission.{$this->context}.edit");
        $this->createPermission = \Config::get("permission.{$this->context}.create");

        //Is?
        $this->isAdmin = $this->data['isAdmin'] = $this->currentUser->hasAccess($this->adminPermission);
        $this->isSalesman = $this->data['isSalesman'] = $this->currentUser->hasAccess(\Config::get("permission.{$this->context}.salesman"));
        $this->isCcare = $this->data['isCcare'] = $this->currentUser->hasAccess(\Config::get("permission.{$this->context}.ccare"));

        //Can?
        $this->canCreate = $this->data['canCreate'] = $this->currentUser->hasAccess($this->createPermission);
        $this->canCreateSalesman = $this->data['canCreateSalesman'] = $this->currentUser->hasAccess(\Config::get("permission.{$this->context}.create-salesman"));
        $this->canCreateOnDelivery = $this->data['canCreateOnDelivery'] = $this->currentUser->hasAccess(\Config::get("permission.{$this->context}.create-ondelivery"));
        $this->canCreateInvoiceCancelled = $this->data['canCreateInvoiceCancelled'] = $this->currentUser->hasAccess(\Config::get("permission.{$this->context}.create-invoice-cancelled"));
        $this->canEdit = $this->data['canEdit'] = $this->currentUser->hasAccess($this->editPermission);
    }
    
    public function getIndex()
    {
        return Redirect::to('/'.$this->rootURL.'/overview');
    }

    public function getOverview()
    {

    }

    public function getForms($type=false,$page=1)
    {
        $limitPerPage = 15;

        $this->pageTitle = 'Forms';

        /*
         * Register Filters
         */

        $this->filter['filter_start_date']  = ['name'=>'Start Date',
                                                    'value' => Input::get('filter_start_date'),
                                                    'enabled' => Input::has('filter_start_date')
                                                ];

        $this->filter['filter_end_date']    = ['name'=>'End Date',
                                                    'value' => Input::get('filter_end_date'),
                                                    'enabled' => Input::has('filter_end_date')
                                                ];

        $this->filter['filter_customer_code'] = ['name'=>'Customer',
                                                'value' => Input::has('filter_customer_code') ? JdeCustomer::find(Input::get('filter_customer_code'))->getReadableName() : false,
                                                'enabled' => Input::has('filter_customer_code')
                                                ];
        
        $this->filter['filter_node_definition_id'] = ['name'=>'Current Step',
                                                    'value' => Input::has('filter_node_definition_id') ? SwiftNodeDefinition::find(Input::get('filter_node_definition_id'))->label :false,
                                                    'enabled' => Input::has('filter_node_definition_id')
                                                    ];

        $this->filter['filter_owner_user_id'] = ['name' => 'Owner',
                                                    'value' => Input::has('filter_owner_user_id') ? Sentry::findUserById(Input::get('filter_owner_user_id'))->first_name." ".Sentry::findUserById(Input::get('filter_owner_user_id'))->last_name : false,
                                                    'enabled' => Input::has('filter_owner_user_id')
                                                ];

        $this->filter['filter_driver_id'] = ['name' => 'Driver',
                                                'value' => Input::has('filter_driver_id') ? SwiftDriver::find(Input::get('filter_driver_id'))->name : false,
                                                'enabled' => Input::has('filter_driver_id')
                                            ];

        /*
         * Filter Lists
         */
        $this->data['filter_list_owners'] = \User::remember(30)
                                            ->has('pr')
                                            ->orderBy('first_name','ASC')
                                            ->orderBy('last_name','ASC')
                                            ->get();

        $this->data['filter_list_node_definition'] = \SwiftNodeDefinition::remember(60)
                                                    ->whereHas('workflow',function($q){
                                                        return $q->where('name','=',$this->context);
                                                    })
                                                    ->orderBy('id','ASC')
                                                    ->get();

        $this->data['filter_list_drivers'] = \SwiftDriver::remember(30)
                                            ->whereHas('pickup',function($q){
                                                return $q->where('pickable_type','SwiftPR');
                                            })
                                            ->orderBy('name','ASC')
                                            ->get();

        $this->data['filter_list_customers'] = \JdeCustomer::remember(30)
                                                ->has('pr')
                                                ->orderBy('ALPH','ASC')
                                                ->get();

        //Check user group
        if($type===false)
        {
            if(!$this->isAdmin)
            {
                //Set defaults
                if($this->canCreate || $this->isSalesman)
                {
                    $type='mine';
                }
                elseif($this->canEdit)
                {
                    $type='inprogress';
                }
                else
                {
                    $type = 'all';
                }
            }
            else
            {
                //Is Admin
                $type='all';
            }
        }

        /*
         * Let's Start Order Query
         */
        $prquery = \SwiftPR::query();

        switch($type)
        {
            case 'inprogress':
                $prquery->orderBy('updated_at','desc')->whereHas('workflow',function($q){
                    return $q->where('status','=',SwiftWorkflowActivity::INPROGRESS);
                });
                break;
            case 'rejected':
                $prquery->orderBy('updated_at','desc')->whereHas('workflow',function($q){
                   return $q->where('status','=',SwiftWorkflowActivity::REJECTED);
                });
                break;
            case 'completed':
                $prquery->orderBy('updated_at','desc')->whereHas('workflow',function($q){
                   return $q->where('status','=',SwiftWorkflowActivity::COMPLETE);
                });
                break;
            case 'starred':
                $prquery->orderBy('updated_at','desc')->whereHas('flag',function($q){
                   return $q->where('type','=',SwiftFlag::STARRED,'AND')->where('user_id','=',$this->currentUser->id,'AND')->where('active','=',SwiftFlag::ACTIVE);
                });
                break;
            case 'important':
                $prquery->orderBy('updated_at','desc')->whereHas('flag',function($q){
                   return $q->where('type','=',SwiftFlag::IMPORTANT,'AND');
                });
                break;
            case 'recent':
                $prquery->join('swift_recent',function($join) use ($prquery){
                    $join->on('swift_recent.recentable_type','=',DB::raw('"SwiftOrder"'));
                    $join->on('swift_recent.recentable_id','=','swift_order.id');
                })->orderBy('swift_recent.updated_at','DESC')->select('swift_order.*');
                break;
            case 'all':
                $prquery->orderBy('updated_at','desc');
                break;
        }

        //The Filters
        foreach($this->filter as $k=>$v)
        {
            if($v['enabled'])
            {
                $filterVal = Input::get($k);
                switch($k)
                {
                    case 'filter_driver_id':

                        break;
                    case 'filter_node_definition_id':
                        $prquery->whereHas('workflow',function($q) use($filterVal){
                           return $q->whereHas('nodes',function($q) use($filterVal){
                               return $q->where('node_definition_id','=',$filterVal);
                           });
                        });
                        break;
                    case 'filter_owner_user_id':
                        $prquery->where('owner_user_id','=',$filterVal);
                        break;
                    case 'filter_customer_code':
                        $prquery->where('customer_code','=',$filterVal);
                        break;
                    case 'filter_start_date':
                        $prquery->where('created_at','>=',$filterVal);
                        break;
                    case 'filter_end_date':
                        $prquery->where('created_at','<=',$filterVal);
                        break;
                }
            }
        }

        $form_count = $prquery->count();

        $prquery->take($limitPerPage);
        if($page > 1)
        {
            $query->offset(($page-1)*$limitPerPage);
        }
        $forms = $prquery->get();

/*
         * Fetch latest history;
         */
        foreach($forms as $k => &$f)
        {

            //Set Current Workflow Activity
            $f->current_activity = WorkflowActivity::progress($f);

            //If in progress, we filter
            if($type == 'inprogress')
            {
                $hasAccess = false;
                /*
                 * Loop through node definition and check access
                 */
                if(isset($f->current_activity['definition']))
                {
                    foreach($f->current_activity['definition'] as $d)
                    {
                        if(NodeActivity::hasAccess($d,SwiftNodePermission::RESPONSIBLE))
                        {
                            $hasAccess = true;
                            break;
                        }
                    }
                }

                /*
                 * No Access : We Remove order from list
                 */
                if(!$hasAccess)
                {
                    unset($forms[$k]);
                    $formsCount--;
                    continue;
                }
            }

            //Set Revision
            $f->revision_latest = Helper::getMergedRevision($f->revisionRelations,$f);

            //Set Starred/important
            $f->flag_starred = Flag::isStarred($f);
            $f->flag_important = Flag::isImportant($f);
            $f->flag_read = Flag::isRead($f);
        }
        
        $this->data['forms'] = $forms;
        $this->data['count'] = $form_count;
        $this->data['type'] = $type;
        $this->data['page'] = $page;
        $this->data['limit_per_page'] = $limitPerPage;
        $this->data['filter_string'] = "?".$_SERVER['QUERY_STRING'];
        $this->data['filter'] = $this->filter;
        $this->data['filter_on'] = (boolean)count(array_filter($this->filter,function($v){
                                        return $v['enabled'];
                                    }));
        $this->data['total_pages'] = ceil($this->data['count']/$limitPerPage);
        $this->data['pageTitle'] = "Cheque Issue - ".ucfirst($type);
        $this->data['canEdit'] = $this->canEdit;
        
        return $this->makeView('product-returns/forms');
    }

    private function form($id,$edit=false)
    {
        $pr_id = Crypt::decrypt($id);
        $pr = SwiftPR::getById($pr_id);

        if($pr)
        {
            /*
             * Set Read
             */

            if(!Flag::isRead($pr))
            {
                Flag::toggleRead($pr);
            }

            /*
             * Enable Commenting
             */
            $this->enableComment($pr);

            $this->data['current_activity'] = \WorkflowActivity::progress($pr,$this->context);
            $this->data['activity'] = \Helper::getMergedRevision($pr->revisionRelations,$pr);
            $this->pageTitle = $pr->getReadableName();
            $this->data['form'] = $pr;
            $this->data['flag_important'] = \Flag::isImportant($pr);
            $this->data['flag_starred'] = \Flag::isStarred($pr);
            
            $order_type = \SwiftErpOrder::$types;
            //Remove Order - S9
            unset($order_type[array_search(SwiftErpOrder::TYPE_ORDER_AP,\SwiftErpOrder::$types)]);
            $this->data['drivers'] = json_encode(Helper::jsonobject_encode(SwiftPRDriver::getAll()));
            $this->data['order_type'] = json_encode(Helper::jsonobject_encode($order_type));
            $this->data['pr_type'] = json_encode(Helper::jsonobject_encode(SwiftPR::$type));
            $this->data['approval_code'] = json_encode(Helper::jsonobject_encode(SwiftApproval::$approved));
            $this->data['product_reason_codes'] = json_encode(Helper::jsonobject_encode(SwiftPRReason::getAll()));
            $this->data['tags'] = json_encode(\Helper::jsonobject_encode(\SwiftTag::$prTags));
            $this->data['owner'] = Helper::getUserName($pr->owner_user_id,$this->currentUser);
            $this->data['isOwner'] = $pr->isOwner();
            $this->data['edit'] = $edit;
            $this->data['publishOwner'] = $this->data['driverInfo'] = $this->data['addProduct'] = false;
            $pr->encrypted_id = \Crypt::encrypt($pr->id);

            if($edit === true)
            {
                if($this->data['current_activity']['status'] == \SwiftWorkflowActivity::INPROGRESS)
                {
                    if(!array_key_exists('definition_obj',$this->data['current_activity']))
                    {
                        /*
                         * Detect buggy workflows
                         * Update on the spot
                         */
                        \WorkflowActivity::update($acp);
                    }
                    else
                    {
                        foreach($this->data['current_activity']['definition_obj'] as $d)
                        {
                            if($d->data != "")
                            {
                                if(isset($d->data->publishOwner) && ($this->isAdmin || $pr->isOwner()))
                                {
                                    $this->data['publishOwner'] = true;
                                    break;
                                }

                                if(isset($d->data->driverInfo) && ($this->currentUser->hasAccess(Config::get("permission.{$this->context}.pickup")) || $this->isAdmin))
                                {
                                    $this->data['driverInfo'] = true;
                                    break;
                                }

                                if(isset($d->data->addProduct) && ($pr->isOwner() || $this->isAdmin))
                                {
                                    $this->data['addProduct'] = true;
                                    break;
                                }
                            }
                        }
                    }
                }
            }

            //Save recently viewed form
            Helper::saveRecent($pr,$this->currentUser);

            return $this->makeView("$this->context/edit");
        }
        else
        {
            return parent::notfound();
        }
    }

    public function getCreate($type = SwiftPR::SALESMAN)
    {
        if(!$this->canCreate)
        {
            return parent::forbidden();
        }

        $this->data['type'] = $type;

        /*
         * Permissions
         */
        switch($type)
        {
            case SwiftPR::SALESMAN:
                if(!$this->currentUser->hasAccess(\Config::get("permission.{$this->context}.create-salesman")))
                {
                    return parent::forbidden();
                }
                break;
            case SwiftPR::ON_DELIVERY:
                if(!$this->currentUser->hasAccess(\Config::get("permission.{$this->context}.create-ondelivery")))
                {
                    return parent::forbidden();
                }
                break;
            case SwiftPR::INVOICE_CANCELLED:
                if(!$this->currentUser->hasAccess(\Config::get("permission.{$this->context}.create-invoice-cancelled")))
                {
                    return parent::forbidden();
                }
                else
                {
                    $this->pageTitle = 'Create - Invoice Cancelled';
                    return $this->makeView("$this->context/create-invoice-cancelled");
                }
                break;
            default:
                return parent::notfound();
                break;
        }

        $this->pageTitle = 'Create';
        return $this->makeView("$this->context/create");
        
    }

    public function postCreate()
    {
        /*
         * validation
         */

        if((int)Input::get('customer_code') === 0)
        {
            return Reaponse::make('Please select a customer',500);
        }
        else
        {
            if(!JdeCustomer::find(Input::get('customer_code')))
            {
                return Response::make('Please select an existing customer',500);
            }
        }

        $pr = new SwiftPR([
            'type' => $type,
            'customer_code' => Input::get('customer_code'),
            'owner_user_id' => $this->currentUser->id
        ]);

        if($pr->save())
        {

        }
        else
        {
            return Response::make("Save unsuccessful",500);
        }
    }

    public function postCreateInvoiceCancelled()
    {
        //permissions
        if(!$this->currentUser->hasAccess(\Config::get("permission.{$this->context}.create-invoice-cancelled")))
        {
            return \Response::make("You don't have permission for this action",500);
        }

        if(Input::has('invoice_code'))
        {
            $invoiceCode = Input::get('invoice_code');
            $lines = JdeSales::getProducts((int)$invoiceCode);
            if(count($lines))
            {
                $invoiceCancelledId = SwiftPRReason::getInvoiceCancelledScottId();

                /*Check if form already exists*/

                $formExist = \SwiftPR::where('customer_code','=',$lines->first()->AN8)
                            ->whereHas('workflow',function($q){
                                //Status = Inprogress or Complete
                                return $q->where('status','!=',\SwiftWorkflowActivity::REJECTED);
                            })
                            ->whereHas('product',function($q) use ($invoiceCancelledId,$invoiceCode){
                                return $q->where('reason_id','=',$invoiceCancelledId)
                                         ->where('invoice_id','=',$invoiceCode,'AND');
                            })->get();
                            
                if(count($formExist) > 0 )
                {
                    return Response::make("Invoice already cancelled, <a href='".Helper::generateURL($formExist->first())."' class='pjax'>Click here to view form</a>",500);
                }

                \Queue::push('Helper@saveInvoiceCancelled',
                            ['invoice_id'=>$invoiceCode,
                             'user_id'=>$this->currentUser->id,
                             'context'=>$this->context]);

                return \Response::make("Invoice with Number: ".$invoiceCode." is being cancelled.");

            }
            else
            {
                return \Response::make("Invoice not found",500);
            }
        }
        else
        {
            return \Response::make("Please input an invoice code");
        }
    }

    public function getInvoiceProducts($invoice_code)
    {
        if($invoice_code > 0)
        {
            $lines = JdeSales::getProducts($invoice_code);
            if(count($lines))
            {
                return Response::make(\View::make("product-returns/invoice-cancelled-products",['lines'=>$lines])->render());
            }
            else
            {
                return Response::make("Invoice number not found",500);
            }
        }
        return Response::make("No invoice number",500);
    }
}
    