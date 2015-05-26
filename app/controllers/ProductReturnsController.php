<?php

/*
 * Product Returns Controller
 *
 * URL: product-returns
 */

class ProductReturnsController extends UserController {
    
    public function __construct(){
        parent::__construct();
        $this->pageName = "Product Returns";
        $this->rootURL = $this->data['rootURL'] = $this->context = $this->data['context'] = "product-returns";
        $this->adminPermission = \Config::get("permission.{$this->context}.admin");
        $this->viewPermission = \Config::get("permission.{$this->context}.view");
        $this->editPermission = \Config::get("permission.{$this->context}.edit");
        $this->createPermission = \Config::get("permission.{$this->context}.create");

        //Is?
        $this->isAdmin = $this->data['isAdmin'] = $this->currentUser->hasAccess($this->adminPermission);
        $this->isSalesman = $this->data['isSalesman'] = $this->currentUser->hasAccess(\Config::get("permission.{$this->context}.salesman"));
        $this->isRetailMan = $this->data['isRetailMan'] = $this->currentUser->hasAccess(\Config::get("permission.{$this->context}.retailman"));
        $this->isCcare = $this->data['isCcare'] = $this->currentUser->hasAccess(\Config::get("permission.{$this->context}.ccare"));
        $this->isStorePickup = $this->data['isStorePickup'] = $this->currentUser->hasAccess(\Config::get("permission.{$this->context}.storepickup"));
        $this->isStoreReception = $this->data['isStoreReception'] = $this->currentUser->hasAccess(\Config::get("permission.{$this->context}.storereception"));
        $this->isStoreValidation = $this->data['isStoreValidation'] = $this->currentUser->hasAccess(\Config::get("permission.{$this->context}.storevalidation"));
        $this->isCreditor = $this->data['isCreditor'] = $this->currentUser->hasAccess(\Config::get("permission.{$this->context}.creditor"));

        //Can?
        $this->canCreate = $this->data['canCreate'] = $this->currentUser->hasAccess($this->createPermission);
        $this->canCreateSalesman = $this->data['canCreateSalesman'] = $this->currentUser->hasAccess(\Config::get("permission.{$this->context}.create-salesman"));
        $this->canCreateOnDelivery = $this->data['canCreateOnDelivery'] = $this->currentUser->hasAccess(\Config::get("permission.{$this->context}.create-ondelivery"));
        $this->canCreateInvoiceCancelled = $this->data['canCreateInvoiceCancelled'] = $this->currentUser->hasAccess(\Config::get("permission.{$this->context}.create-invoice-cancelled"));
        $this->canEdit = $this->data['canEdit'] = $this->currentUser->hasAccess($this->editPermission);
    }
    
    public function getIndex()
    {
        return \Redirect::to('/'.$this->rootURL.'/overview');
    }

    /*
     * GET: Overview View
     *
     * @return \Illuminate\Support\Facades\Response
     */
    public function getOverview()
    {
        $this->adminList();
    }

    /*
     * GET: Forms View
     *
     * @param boolean $type
     * @param int $page
     * @return \Illuminate\Support\Facades\Response
     */
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
                    return $q->where('status','=',\SwiftWorkflowActivity::INPROGRESS);
                });
                break;
            case 'rejected':
                $prquery->orderBy('updated_at','desc')->whereHas('workflow',function($q){
                   return $q->where('status','=',\SwiftWorkflowActivity::REJECTED);
                });
                break;
            case 'completed':
                $prquery->orderBy('updated_at','desc')->whereHas('workflow',function($q){
                   return $q->where('status','=',\SwiftWorkflowActivity::COMPLETE);
                });
                break;
            case 'starred':
                $prquery->orderBy('updated_at','desc')->whereHas('flag',function($q){
                   return $q->where('type','=',\SwiftFlag::STARRED,'AND')->where('user_id','=',$this->currentUser->id,'AND')->where('active','=',SwiftFlag::ACTIVE);
                });
                break;
            case 'important':
                $prquery->orderBy('updated_at','desc')->whereHas('flag',function($q){
                   return $q->where('type','=',\SwiftFlag::IMPORTANT,'AND');
                });
                break;
            case 'recent':
                $prquery->join('swift_recent',function($join) use ($prquery){
                    $join->on('swift_recent.recentable_type','=',\DB::raw('"SwiftOrder"'));
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
            $f->current_activity = \WorkflowActivity::progress($f);

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
                        if(\NodeActivity::hasAccess($d,\SwiftNodePermission::RESPONSIBLE))
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
            $f->revision_latest = \Helper::getMergedRevision($f->revisionRelations,$f);

            //Set Starred/important
            $f->flag_starred = \Flag::isStarred($f);
            $f->flag_important = \Flag::isImportant($f);
            $f->flag_read = \Flag::isRead($f);
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

    /*
     * Form Data Processing
     *
     * @param string $id
     * @param boolean $edit
     * @return \Illuminate\Support\Facades\Response
     */
    private function form($id,$edit=false)
    {
        $pr_id = \Crypt::decrypt($id);
        $pr = \SwiftPR::getById($pr_id);

        if($pr)
        {
            /*
             * Set Read
             */

            if(!\Flag::isRead($pr))
            {
                \Flag::toggleRead($pr);
            }

            /*
             * Enable Commenting
             */
            $this->enableComment($pr);

            /*
             * View Variables
             */
            $this->data['current_activity'] = \WorkflowActivity::progress($pr,$this->context);
            $this->data['activity'] = \Helper::getMergedRevision($pr->revisionRelations,$pr);
            $this->pageTitle = $pr->getReadableName();
            $this->data['form'] = $pr;
            $this->data['flag_important'] = \Flag::isImportant($pr);
            $this->data['flag_starred'] = \Flag::isStarred($pr);
            $this->data['erporder_status'] = json_encode(\Helper::jsonobject_encode(\SwiftErpOrder::$status));
            $this->data['erporder_type'] = json_encode(\Helper::jsonobject_encode(\SwiftErpOrder::$prType));
            $this->data['pickup_status'] = json_encode(\Helper::jsonobject_encode(\SwiftPickup::$pr_status));
            $this->data['drivers'] = json_encode(\Helper::jsonobject_encode(\SwiftDriver::getAll()));
            $this->data['pr_type'] = json_encode(\Helper::jsonobject_encode(\SwiftPR::$type));
            $this->data['approval_code'] = json_encode(\Helper::jsonobject_encode(\SwiftApproval::$approved));
            $this->data['product_reason_codes'] = json_encode(\Helper::jsonobject_encode(\SwiftPRReason::getAll()));
            $this->data['product_reason_codes_array'] = \SwiftPRReason::getAll();
            $this->data['tags'] = json_encode(\Helper::jsonobject_encode(\SwiftTag::$prTags));
            $this->data['owner'] = \Helper::getUserName($pr->owner_user_id,$this->currentUser);
            $this->data['isOwner'] = $pr->isOwner();
            $this->data['edit'] = $edit;
            $this->data['publishOwner'] = $this->data['publishPickup'] =
                                            $this->data['publishReception'] =
                                            $this->data['publishCreditNote'] =
                                            $this->data['driverInfo'] =
                                            $this->data['addProduct'] = false;
            $pr->encrypted_id = \Crypt::encrypt($pr->id);

            //If we can edit the form
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

                    //Set the controls

                    foreach($this->data['current_activity']['definition_obj'] as $d)
                    {
                        if($d->data != "")
                        {
                            //Add Product
                            if(isset($d->data->publishOwner) &&
                                !$pr->approval()->where('type','=',\SwiftApproval::PR_REQUESTER)->count() &&
                                ($this->isAdmin || $pr->isOwner()))
                            {
                                $this->data['publishOwner'] = true;
                                if(isset($d->data->addProduct) && $pr->isOwner())
                                {
                                    $this->data['addProduct'] = true;
                                }
                                break;
                            }

                            //Store Pickup
                            if(isset($d->data->publishPickup) && ($this->isAdmin || $this->isStorePickup))
                            {
                                $this->data['publishPickup'] = true;
                                break;
                            }

                            //Store Reception
                            if(isset($d->data->publishReception) && ($this->isAdmin || $this->isStoreReception))
                            {
                                $this->data['publishReception'] = true;
                                break;
                            }

                            //Credit Note
                            if(isset($d->data->publishCreditNote) && ($this->isAdmin || $this->isCreditor))
                            {
                                $this->data['publishCreditNote'] = true;
                                break;
                            }

                            //Driver Information
                            if(isset($d->data->driverInfo) && ($this->isAdmin || $this->isStorePickup))
                            {
                                $this->data['driverInfo'] = true;
                                break;
                            }
                        }
                    }

                    //Admins can edit products anytime
                    if($this->isAdmin)
                    {
                        $this->data['addProduct'] = true;
                    }
                }
            }

            //Save recently viewed form
            \Helper::saveRecent($pr,$this->currentUser);

            return $this->makeView("$this->context/edit");
        }
        else
        {
            return parent::notfound();
        }
    }

    /*
     * GET: Read-only View
     *
     * @param string $id
     * @return \Illuminate\Support\Facades\Response
     */
    public function getView($id)
    {
        if($this->currentUser->hasAnyAccess([$this->editPermission,$this->adminPermission]))
        {
            return \Redirect::action('ProductReturnsController@getEdit',array('id'=>$id));
        }
        elseif($this->currentUser->hasAccess($this->viewPermission))
        {
            return $this->form($id,false);
        }
        else
        {
            return parent::forbidden();
        }
    }

    /*
     * GET: Edit View
     *
     * @param string $id
     * @return \Illuminate\Support\Facades\Response
     */
    public function getEdit($id)
    {
        if($this->currentUser->hasAnyAccess([$this->editPermission,$this->adminPermission]))
        {
            return $this->form($id,true);
        }
        elseif($this->currentUser->hasAccess($this->viewPermission))
        {
            return \Redirect::action('ProductReturnsController@getView',array('id'=>$id));
        }
        else
        {
            return parent::forbidden();
        }
    }

    /*
     * GET: Create Form View
     *
     * @param int $type
     * @return \Illuminate\Support\Facades\Response
     */
    public function getCreate($type = SwiftPR::SALESMAN)
    {
        if(!$this->canCreate)
        {
            return parent::forbidden();
        }

        if(!in_array($type,[\SwiftPR::ON_DELIVERY,\SwiftPR::SALESMAN]))
        {
            return parent::notfound();
        }

        $this->data['type'] = $type;

        /*
         * Permissions
         */
        switch($type)
        {
            case \SwiftPR::SALESMAN:
                if(!$this->currentUser->hasAccess(\Config::get("permission.{$this->context}.create-salesman")))
                {
                    return parent::forbidden();
                }
                break;
            case \SwiftPR::ON_DELIVERY:
                if(!$this->currentUser->hasAccess(\Config::get("permission.{$this->context}.create-ondelivery")))
                {
                    return parent::forbidden();
                }
                break;
            case \SwiftPR::INVOICE_CANCELLED:
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
        
        $this->data['type_name'] = \SwiftPR::$type[$type];
        $this->data['type'] = $type;
        $this->pageTitle = 'Create';
        return $this->makeView("$this->context/create");
        
    }

    /*
     * Save new invoice cancelled Form
     *
     * @param int $type
     * @return \Illuminate\Support\Facades\Response
     */

    public function postCreate($type)
    {

        if(!$this->canCreate && !$this->canCreateSalesman && !$this->canCreateOnDelivery)
        {
            return parent::forbidden();
        }

        /*
         * validation
         */

        if((int)\Input::get('customer_code') === 0)
        {
            return \Response::make('Please select a customer',500);
        }
        else
        {
            if(!\JdeCustomer::find(\Input::get('customer_code')))
            {
                return \Response::make('Please select an existing customer',500);
            }
        }

        if(!in_array($type,[\SwiftPR::SALESMAN,\SwiftPR::ON_DELIVERY]))
        {
            return \Response::make('Type of product return is not valid',500);
        }

        return $this->process()->create($type);
    }

    /*
     * Save new invoice cancelled Form
     *
     * @return \Illuminate\Support\Facades\Response
     */

    public function postCreateInvoiceCancelled()
    {
        //permissions
        if(!$this->currentUser->hasAccess(\Config::get("permission.{$this->context}.create-invoice-cancelled")))
        {
            return \Response::make("You don't have permission for this action",500);
        }

        return $this->process('SwiftPR')->createInvoiceCancelled();
    }

    /*
     * GET: Publish Owner
     *
     * @param string $form_id
     * @return \Illuminate\Support\Facades\Response
     */

    public function postPublishOwner($form_id)
    {
        /*
         * Check Permissions
         */
        if(!$this->currentUser->hasAnyAccess([$this->adminPermission,$this->editPermission]))
        {
            return parent::forbidden();
        }

        $form = \SwiftPR::with('product','product.discrepancy')->find(Crypt::decrypt($form_id));

        if($form)
        {
            if(!$this->isAdmin && !$form->isOwner())
            {
                return \Response::make("You don't have permission to publish this form" ,500);
            }

            if(!count($form->product))
            {
                return \Response::make('Please add some products to your form',500);
            }

            //Validation

            foreach($form->product as $p)
            {
                switch($form->type)
                {
                    case \SwiftPR::ON_DELIVERY:
                        if($p->qty_pickup === null || $p->qty_pickup < 0)
                        {
                            return \Response::make("Please set a valid quantity at pickup for '$p->name' (ID: $p->id)",500);
                        }

                        if($p->qty_pickup !== ($p->qty_triage_pickup + $p->qty_triage_disposal))
                        {
                            return \Response::make("Please make sure that quantity pickup tallies with quantity triage for '$p->name' (ID: $p->id)",500);
                        }
                    case \SwiftPR::SALESMAN:
                        if($p->jde_itm === null)
                        {
                            return \Response::make("Please set a Product for (ID: $p->id)",500);
                        }

                        if($p->qty_client === null || $p->qty_client < 0)
                        {
                            return \Response::make("Please set a valid quantity at client for '$p->name' (ID: $p->id)",500);
                        }

                        if($p->reason_id === null)
                        {
                            return \Response::make("Please set a reason for '$p->name' (ID: $p->id)",500);
                        }
                }
            }

            if($form->type === \SwiftPR::ON_DELIVERY)
            {
                if($form->paper_number === null)
                {
                    return \Response::make("Please enter an RFRF Paper number",500);
                }
            }

            /*
             * All Clear -  we process
             */

            //Add the Approval

            $approval = $form->approval()
                        ->where('type','=',SwiftApproval::PR_REQUESTER,'AND')
                        ->where('approved','=',SwiftApproval::APPROVED)
                        ->count();
            if($approval)
            {
                \Queue::push('WorkflowActivity@updateTask',array('class'=>get_class($form),'id'=>$form->id,'user_id'=>$this->currentUser->id));
                /*
                 * Check if form has already been approved
                 */
                return \Response::make('Form already approved. System is busy processing',200);
            }
            else
            {
                $approvalSaved = $form->approval()->save(
                   new \SwiftApproval([
                        'type' => \SwiftApproval::PR_REQUESTER,
                        'approved' => \SwiftApproval::APPROVED,
                        'approval_user_id' => $this->currentUser->id
                   ])
                );

                if($approvalSaved)
                {
                    \Queue::push('WorkflowActivity@updateTask',array('class'=>get_class($form),'id'=>$form->id,'user_id'=>$this->currentUser->id));
                    return \Response::make('success');
                }
                else
                {
                    return \Response::make('Failed to approve form',400);
                }
            }
        }
        else
        {
            return \Response::make("Form not found",500);
        }

        return \Response::make("Unable to complete action",500);
        
    }

    /*
     * PUT: General Info
     *
     * @param string $form_id
     * @return \Illuminate\Support\Facades\Response
     */

    public function putGeneralinfo($form_id)
    {
        //Basic Permission Check
        if($this->currentUser->hasAnyAccess([$this->editPermission,$this->adminPermission]))
        {
            return parent::forbidden();
        }

        return $this->process('SwiftPR')->save($form_id);

    }

    /*
     * PUT: Product
     *
     * @return \Illuminate\Support\Facades\Response
     */

    public function putProduct($form_id)
    {
        /*
         * Basic Permission Check
         */
        if(!$this->currentUser->hasAnyAccess([$this->editPermission,$this->adminPermission]))
        {
            return parent::forbidden();
        }

        return $this->process('SwiftPRProduct')->save($form_id);
    }

    /*
     * DELETE: Product
     *
     * @return \Illuminate\Support\Facades\Response
     */
    public function deleteProduct()
    {
        /*
         * Check Permissions
         */
        if(!$this->currentUser->hasAnyAccess([$this->adminPermission,$this->editPermission]))
        {
            return parent::forbidden();
        }

        return $this->process('SwiftPRProduct')->delete();
    }

    /*
     * PUT: JDE Order
     *
     * @param string $pr_id
     * @param
     */
    public function putErporder($pr_id)
    {
        /*
         * Check Permissions
         */
        if(!$this->isAdmin && !$this->isCcare)
        {
            return parent::forbidden();
        }

        return $this->process('SwiftErpOrder')->saveByParent($pr_id,\Config::get("context.$this->context"));

    }

    public function deleteErporder()
    {
        /*
         * Check Permissions
         */
        if(!$this->currentUser->hasAnyAccess([$this->adminPermission,$this->ccarePermission]))
        {
            return parent::forbidden();
        }

        return $this->process('SwiftErpOrder')->delete();
    }

    /*
     * Approval of products for Retail Manager
     *
     * @param int $type
     * @param string $product_id
     *
     * @return \Illuminate\Support\Facades\Response
     */
    public function putProductApproval($type,$product_id)
    {
        /*  
         * Check Permissions
         */        
        if(!$this->isRetailMan)
        {
            return parent::forbidden();
        }
        
        $product = \SwiftPRProduct::find(\Crypt::decrypt($product_id));
        
        if($product)
        {
            if(\Input::get('name') == "approval_approved" && in_array(\Input::get('value'),array(\SwiftApproval::REJECTED,\SwiftApproval::APPROVED,\SwiftApproval::PENDING)))
            {
                switch((int)$type)
                {
                    case \SwiftApproval::PR_RETAILMAN:
                        if(is_numeric(\Input::get('pk')))
                        {
                            /*
                             * New Entry
                             */
                            //All Validation Passed, let's save
                            $approval = new \SwiftApproval(array('type'=>(int)$type,'approval_user_id'=>$this->currentUser->id, 'approved' => \Input::get('value')));
                            if($product->approvalretailman()->save($approval))
                            {
                                $pr = $product->pr()->first();
                                \Queue::push('WorkflowActivity@updateTask',array('class'=>get_class($pr),'id'=>$pr->id,'user_id'=>$this->currentUser->id));
                                return \Response::make(json_encode(['encrypted_id'=>\Crypt::encrypt($approval->id),'id'=>$approval->id]));
                            }
                            else
                            {
                                return \Response::make('Failed to save. Please retry',400);
                            }

                        }
                        else
                        {
                            $approval = \SwiftApproval::find(\Crypt::decrypt(Input::get('pk')));
                            if(count($approval))
                            {
                                $approval->approved = \Input::get('value') == "" ? null : \Input::get('value');
                                if($approval->save())
                                {
                                    $pr = $product->pr()->first();
                                    \Queue::push('WorkflowActivity@updateTask',array('class'=>get_class($pr),'id'=>$pr->id,'user_id'=>$this->currentUser->id));
                                    return \Response::make('Success');
                                }
                                else
                                {
                                    return \Response::make('Failed to save. Please retry',400);
                                }
                            }
                            else
                            {
                                return \Response::make('Error saving approval information: Invalid PK',400);
                            }
                        }
                        break;
                    default:
                        return \Response::make('Type of approval unknown',400);
                        break;
                }
            }
            else
            {
                return \Response::make('Invalid Request',400);
            }
        }
        else
        {
            return \Response::make('Product not found',404);
        }
    }

    /*
     * Approval Comment for Retail Man
     *
     * @param int $type
     * @param string $product_id
     * @return \Illuminate\Support\Facades\Response
     */
    public function putProductapprovalcomment($type,$product_id)
    {
        /*
         * Check Permissions
         */
        if(!$this->isRetailMan)
        {
            return parent::forbidden();
        }

        $product = \SwiftPRProduct::find(\Crypt::decrypt($product_id));

        if(count($product))
        {
            if(\Input::get('name') == "approval_comment")
            {
                switch((int)$type)
                {
                    case \SwiftApproval::PR_RETAILMAN:
                        if(is_numeric(\Input::get('pk')))
                        {
                            return \Response::make('Please approve the product first',400);
                        }
                        else
                        {
                            $approval = \SwiftApproval::find(\Crypt::decrypt(\Input::get('pk')));
                            if(count($approval))
                            {
                                if($approval->approved == \SwiftApproval::REJECTED && trim(\Input::get('value'))=="")
                                {
                                    return \Response::make('Please enter a comment for rejected product',400);
                                }

                                //Get Comments
                                $comment = $approval->comments()->first();

                                if(count($comment))
                                {
                                    $comment->comment = trim(\Input::get('value'));
                                    if($comment->save())
                                    {
                                        return \Response::make('Success');
                                    }
                                }
                                else
                                {
                                    $newcomment = new \SwiftComment(['comment'=>trim(\Input::get('value')),'user_id'=>$this->currentUser->id]);
                                    if($approval->comments()->save($newcomment))
                                    {
                                        return \Response::make('Success');
                                    }
                                }
                                return \Response::make('Failed to save. Please retry',400);
                            }
                            else
                            {
                                return \Response::make('Error saving approval comment: Invalid PK',400);
                            }
                        }
                        break;
                    default:
                        return \Response::make('Type of approval unknown',400);
                        break;
                }
            }
            else
            {
                return \Response::make('Invalid Request',400);
            }
        }
        else
        {
            return \Response::make('Product not found',404);
        }
    }

    /*
     * Save Product From Invoice By Form
     *
     * @return string
     */

    public function postSaveProductByInvoice()
    {
        if(\Input::has('pr_id'))
        {
            $pr_id = \Input::get('pr_id');
            $pr = \SwiftPR::find(\Crypt::decrypt($pr_id));

            if($pr)
            {
                /*
                 * Save Products
                 */
                $products = \Input::get('jde_itm',false);
                if($products === false)
                {
                    return \Response::make("Please select at least one product",500);
                }

                $qty_client_included = \Input::has('qty_client_included');
                $qty_pickup_included = \Input::has('qty_pickup_included');

                $invoice_lines = \JdeSales::getProducts(\Input::get('invoice_id'));
                
                foreach($products as $line_number => $jde_itm)
                {
                    //Check if Valid Product ITM
                    if(is_numeric($jde_itm) && \JdeProduct::find($jde_itm))
                    {
                        //Variable Declarations
                        $qty_client = $qty_pickup = $price = $qty_triage_picking = $qty_triage_disposal = $reason_id = $reason_others = null;

                        //Get Invoice Lines
                        $filter = $invoice_lines->filter(function($line) use ($line_number){
                                                return (int)$line->LNID === (int)$line_number;
                                            })->first();

                        //If there are at least one line
                        if($filter)
                        {
                            //Quantity Client
                            if($qty_client_included)
                            {
                                $qty_client = $filter->SOQS;
                            }
                            //Quantity Pickup
                            if($qty_pickup_included)
                            {
                                $qty_pickup = $filter->SOQS;
                                if(\Input::has('qty_to'))
                                {
                                    switch(\Input::get('qty_to'))
                                    {
                                        case 'picking':
                                            $qty_triage_picking = $filter->SOQS;
                                            break;
                                        case 'disposal':
                                            $qty_triage_disposal = $filter->SOQS;
                                            break;
                                    }
                                }
                            }
                            $price = $filter->AEXP/$filter->SOQS;
                        }

                        //Pickup
                        if($pr->type === \SwiftPR::SALESMAN && \Input::has('pickup'))
                        {
                            if(in_array(Input::get('pickup'),[0,1]))
                            {
                                $pickup = \Input::get('pickup');
                            }
                            else
                            {
                                $pickup = 1;
                            }
                        }
                        else
                        {
                            $pickup = 0;
                        }

                        //Reason Id
                        if(\SwiftPRReason::find(\Input::get('reason_id',0)))
                        {
                            $reason_id = \Input::get('reason_id');
                        }

                        //Reason Others

                        if(\Input::get('reason_others',""))
                        {
                            $reason_others = trim(\Input::get('reason_others'));
                        }

                        //Save Product Relationship
                        $pr->product()->save(
                            new SwiftPRProduct([
                                'jde_itm' => $jde_itm,
                                'pickup' => $pickup,
                                'qty_client' => $qty_client,
                                'qty_pickup' => $qty_pickup,
                                'qty_triage_picking' => $qty_triage_picking,
                                'qty_triage_disposal' => $qty_triage_disposal,
                                'reason_id' => $reason_id,
                                'reason_others' => $reason_others,
                                'invoice_id' => \Input::get('invoice_id'),
                                'invoice_recognition' => \SwiftPRProduct::INVOICE_AUTO,
                                'price' => $price
                            ])
                        );
                    }
                    else
                    {
                        return \Response::make("Unable to find product with Id: $jde_itm",500);
                    }
                }

                return \Response::json(["msg"=>"Products added successfully"]);
                
            }
        }

        return \Response::make("Form not found",500);
    }

    /*
     * Document: Save
     * @param string $pr_id
     * @return string
     */
    public function postUpload($pr_id)
    {

        /*
         * Check Permissions
         */
        if(!$this->currentUser->hasAnyAccess([$this->adminPermission,$this->editPermission]))
        {
            return parent::forbidden();
        }

        $pr = SwiftPR::find(Crypt::decrypt($pr_id));
        /*
         * Manual Validation
         */
        if(count($pr))
        {
            if(Input::file('file'))
            {
                $doc = new SwiftPRDocument();
                $doc->document = Input::file('file');
                if($pr->document()->save($doc))
                {
                    echo json_encode(['success'=>1,
                                    'url'=>$doc->getAttachedFiles()['document']->url(),
                                    'id'=>Crypt::encrypt($doc->id),
                                    'updated_on'=>$doc->getAttachedFiles()['document']->updatedAt(),
                                    'updated_by'=>Helper::getUserName($doc->user_id,$this->currentUser)]);
                }
                else
                {
                    return Response::make('Upload failed.',400);
                }
            }
            else
            {
                return Response::make('File not found.',400);
            }
        }
        else
        {
            return Response::make('Product returns form not found',404);
        }
    }

    /*
     * Document: Delete
     *
     * @param string $doc_id
     * @return string
     */

    public function deleteUpload($doc_id)
    {

        /*
         * Check Permissions
         */
        if(!$this->currentUser->hasAnyAccess([$this->adminPermission,$this->editPermission]))
        {
            return parent::forbidden();
        }

        $doc = SwiftAPDocument::find(Crypt::decrypt($doc_id));
        /*
         * Manual Validation
         */
        if(count($doc))
        {
            if($doc->delete())
            {
                echo json_encode(['success'=>1,'url'=>$doc->getAttachedFiles()['document']->url(),'id'=>Crypt::encrypt($doc->id)]);
            }
            else
            {
                return Response::make('Delete failed.',400);
            }
        }
        else
        {
            return Response::make('Document not found',404);
        }
    }

    /*
     * Tags: REST
     */

    public function putTag()
    {
        /*
        * Check Permissions
        */
        if(!$this->currentUser->hasAnyAccess([$this->adminPermission,$this->editPermission]))
        {
            return parent::forbidden();
        }

        if(\Input::get('pk') && !is_numeric(\Input::get('pk')))
        {
            $doc = \SwiftPRDocument::with('tag')->find(\Crypt::decrypt(\Input::get('pk')));
            if($doc)
            {
                //Lets check those tags
                if(count($doc->tag))
                {
                    if(\Input::get('value'))
                    {
                        //It already has some tags
                        //Save those not in table
                        foreach(\Input::get('value') as $val)
                        {
                            $found = false;
                            foreach($doc->tag as $t)
                            {
                                if($t->type == $val)
                                {
                                    $found = true;
                                    break;
                                }
                            }
                            //Save
                            if(!$found)
                            {
                                /*
                                 * Validate dat tag
                                 */
                                if(key_exists($val,\SwiftTag::$prTags))
                                {
                                    $tag = new \SwiftTag(array('type'=>$val));
                                    if(!$doc->tag()->save($tag))
                                    {
                                        return \Response::make('Error: Unable to save tags',400);
                                    }
                                }
                            }
                        }

                        //Delete values from table, not in value array

                        foreach($doc->tag as $t)
                        {
                            $found = false;
                            foreach(\Input::get('value') as $val)
                            {
                                if($val == $t->type)
                                {
                                    $found = true;
                                    break;
                                }
                            }
                            //Delete
                            if(!$found)
                            {
                                if(!$t->delete())
                                {
                                    return \Response::make('Error: Cannot delete tag',400);
                                }
                            }
                        }
                    }
                    else
                    {
                        //Delete all existing tags
                        if(!$doc->tag()->delete())
                        {
                            return \Response::make('Error: Cannot delete tag',400);
                        }
                    }
                }
                else
                {
                    //Alright, just save then
                    foreach(\Input::get('value') as $val)
                    {
                        /*
                         * Validate dat tag
                         */
                        if(key_exists($val,\SwiftTag::$prTags))
                        {
                            $tag = new \SwiftTag(array('type'=>$val));
                            if(!$doc->tag()->save($tag))
                            {
                                return \Response::make('Error: Unable to save tags',400);
                            }
                        }
                        else
                        {
                            return \Response::make('Error: Invalid tags',400);
                        }
                    }
                }
                return \Response::make('Success');
            }
            else
            {
                return \Response::make('Error: Document not found',400);
            }
        }
        else
        {
            return \Response::make('Error: Document number invalid',400);
        }
    }

    /*
     * Cancel Form
     */

    public function postCancel($pr_id)
    {
        /*
         * Check Permissions
         */
        if(!$this->currentUser->hasAccess([$this->adminPermission,$this->editPermission]))
        {
            return parent::forbidden();
        }

        $form = \SwiftPR::find(\Crypt::decrypt($pr_id));

        if(count($form))
        {

            /*
             * Normal User but not creator = no access
             */
            if($this->currentUser->hasAccess($this->editPermission) &&
                !$this->currentUser->isSuperUser() &&
                !$form->isOwner())
            {
                return Response::make('Do not cancel, that which is not yours',400);
            }

            if(\WorkflowActivity::cancel($form))
            {
                return Response::make('Workflow has been cancelled',200);
            }

            return Response::make('Unable to cancel workflow',400);
        }
        else
        {
            return Response::make('A&P Request form not found',404);
        }
    }

    /*
     * AJAX CALLS: Start
     */

    /*
     * Ajax Call to get Invoice Products
     *
     * @return \Illuminate\Support\Facades\Response
     */
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

    /*
     * Ajax Call to get Invoice Products For Form
     *
     * @return \Illuminate\Support\Facades\Response
     */
    public function getInvoiceProductsForForm($invoice_code)
    {
        if($invoice_code > 0 && is_numeric($invoice_code))
        {
            $lines = JdeSales::getProducts($invoice_code);
            if(count($lines))
            {
                return Response::make(\View::make("$this->context/invoice-products-by-form",['lines'=>$lines])->render());
            }
            else
            {
                return Response::make("Invoice number not found",500);
            }
        }
        return Response::make("No invoice number",500);
    }

    /*
     * Ajax call to display help information on workflow status
     *
     * @return string
     */

    public function getHelp($id)
    {
        /*
        * Check Permissions
        */
        if(!$this->currentUser->hasAnyAccess([$this->adminPermission,$this->editPermission]))
        {
            return "You don't have access to this resource.";
        }

        $needPermission = true;

        if($this->currentUser->hasAccess($this->adminPermission))
        {
            $needPermission = false;
        }

        $form = \SwiftPR::find(\Crypt::decrypt($id));
        if(count($form))
        {
            return \WorkflowActivity::progressHelp($form,$needPermission);
        }
        else
        {
            return "We can't find the resource that you were looking for.";
        }
    }

    /*
     * GET list of products by form
     *
     * @return string
     */
    public function getProductsByForm($pr_id)
    {
        $pr = \SwiftPR::getById(Crypt::decrypt($pr_id));
        if($pr)
        {
            $this->data['current_activity'] = \WorkflowActivity::progress($pr,$this->context);
            $this->data['form'] = $pr;
            $this->data['erporder_status'] = json_encode(\Helper::jsonobject_encode(\SwiftErpOrder::$status));
            $this->data['erporder_type'] = json_encode(\Helper::jsonobject_encode(\SwiftErpOrder::$prType));
            $this->data['pickup_status'] = json_encode(\Helper::jsonobject_encode(\SwiftPickup::$pr_status));
            $this->data['pr_type'] = json_encode(\Helper::jsonobject_encode(\SwiftPR::$type));
            $this->data['approval_code'] = json_encode(\Helper::jsonobject_encode(\SwiftApproval::$approved));
            $this->data['product_reason_codes'] = json_encode(\Helper::jsonobject_encode(\SwiftPRReason::getAll()));
            $this->data['tags'] = json_encode(\Helper::jsonobject_encode(\SwiftTag::$prTags));
            $this->data['owner'] = \Helper::getUserName($pr->owner_user_id,$this->currentUser);
            $this->data['isOwner'] = $pr->isOwner();
            $this->data['edit'] = true;
            $this->data['publishOwner'] = $this->data['publishPickup'] =
                                            $this->data['publishReception'] =
                                            $this->data['publishCreditNote'] =
                                            $this->data['driverInfo'] =
                                            $this->data['addProduct'] = false;
            $pr->encrypted_id = \Crypt::encrypt($pr->id);

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

                foreach($this->data['current_activity']['definition_obj'] as $d)
                {
                    if($d->data != "")
                    {
                        if(isset($d->data->publishOwner) && ($this->isAdmin || $pr->isOwner()))
                        {
                            $this->data['publishOwner'] = true;
                            if(isset($d->data->addProduct) && ($pr->isOwner() || $this->isAdmin))
                            {
                                $this->data['addProduct'] = true;
                            }
                            break;
                        }

                        if(isset($d->data->publishPickup) && ($this->isAdmin || $this->isStorePickup))
                        {
                            $this->data['publishPickup'] = true;
                            break;
                        }

                        if(isset($d->data->publishReception) && ($this->isAdmin || $this->isStoreReception))
                        {
                            $this->data['publishReception'] = true;
                            break;
                        }

                        if(isset($d->data->publishCreditNote) && ($this->isAdmin || $this->isCreditor))
                        {
                            $this->data['publishCreditNote'] = true;
                            break;
                        }

                        if(isset($d->data->driverInfo) && ($this->isAdmin || $this->isStorePickup))
                        {
                            $this->data['driverInfo'] = true;
                            break;
                        }
                    }
                }
            }

            return \Response::make(\View::make("$this->context/edit_product_table",$this->data)->render());
        }

        return \Response::make("An error occured fetching the products. Please refresh the page.",500);
    }

    /*
     * AJAX CALLS: End
     */
}
    