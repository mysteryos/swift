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
        $this->rootURL = $this->data['rootURL'] = $this->context = "product-returns";
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
        return Redirect::to('/'.$this->rootURL.'/overview');
    }

    /*
     * GET: Overview View
     *
     * @return \Illuminate\Support\Facades\Response
     */
    public function getOverview()
    {

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

            $this->data['current_activity'] = \WorkflowActivity::progress($pr,$this->context);
            $this->data['activity'] = \Helper::getMergedRevision($pr->revisionRelations,$pr);
            $this->pageTitle = $pr->getReadableName();
            $this->data['form'] = $pr;
            $this->data['flag_important'] = \Flag::isImportant($pr);
            $this->data['flag_starred'] = \Flag::isStarred($pr);
            $this->data['erporder_status'] = json_encode(\Helper::jsonobject_encode(\SwiftErpOrder::$status));
            $this->data['erporder_type'] = json_encode(\Helper::jsonobject_encode(\SwiftErpOrder::$prType));
            $this->data['pickup_status'] = json_encode(\Helper::jsonobject_encode(\SwiftPickup::$pr_status));
            $this->data['drivers'] = json_encode(\Helper::jsonobject_encode(SwiftDriver::getAll()));
            $this->data['pr_type'] = json_encode(\Helper::jsonobject_encode(SwiftPR::$type));
            $this->data['approval_code'] = json_encode(\Helper::jsonobject_encode(SwiftApproval::$approved));
            $this->data['product_reason_codes'] = json_encode(\Helper::jsonobject_encode(SwiftPRReason::getAll()));
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
            return Redirect::action('ProductReturnsController@getEdit',array('id'=>$id));
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
            return Redirect::action('ProductReturnsController@getView',array('id'=>$id));
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
            return \Reaponse::make('Please select a customer',500);
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

        $pr = new SwiftPR([
            'type' => $type,
            'customer_code' => \Input::get('customer_code'),
            'owner_user_id' => $this->currentUser->id
        ]);

        if($pr->save())
        {
            if(\WorkflowActivity::update($pr,$this->context))
            {
                //Story Relate
                \Queue::push('Story@relateTask',array('obj_class'=>get_class($pr),
                                                     'obj_id'=>$pr->id,
                                                     'action'=>\SwiftStory::ACTION_CREATE,
                                                     'user_id'=>$this->currentUser->id,
                                                     'context'=>get_class($pr)));
                //Success
                echo json_encode(['success'=>1,'url'=>\Helper::generateUrl($pr)]);
            }
            else
            {
                return \Response::make("Failed to save workflow",400);
            }
        }
        else
        {
            return \Response::make("Save unsuccessful",500);
        }
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

        if(\Input::has('invoice_code'))
        {
            $invoiceCode = \Input::get('invoice_code');
            $lines = \JdeSales::getProducts((int)$invoiceCode);
            if(count($lines))
            {
                $invoiceCancelledId = \SwiftPRReason::getInvoiceCancelledScottId();

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

    /*
     * PUT: General Info
     *
     * @param string $form_id
     * @return \Illuminate\Support\Facades\Response
     */

    public function putGeneralinfo($form_id)
    {
        $id = \Crypt::decrypt($form_id);
        $pr = \SwiftPR::find($id);

        if($pr)
        {
            if($this->currentUser->hasAccess($this->editPermission))
            {
                switch(Input::get('name'))
                {
                    case 'type':
                        if(!$this->currentUser->isSuperUser())
                        {
                            return parent::forbidden();
                        }
                        break;
                }

                $pr->{Input::get('name')} = Input::get('value') == "" ? null : Input::get('value');
                if($pr->save())
                {
                    Queue::push('WorkflowActivity@updateTask',array('class'=>get_class($pr),'id'=>$pr->id,'user_id'=>$this->currentUser->id));
                    return Response::make('Success', 200);
                }
                else
                {
                    return Response::make('Failed to save. Please retry',400);
                }
            }
            else
            {
                return parent::forbidden();
            }
        }

        return \Response::make("Form not found",500);
    }

    /*
     * PUT: Product
     *
     * @return \Illuminate\Support\Facades\Response
     */

    public function putProduct($form_id)
    {
        $id = \Crypt::decrypt($form_id);
        $form = \SwiftPR::find($id);

        /*
         * Basic Permission Check
         */
        if(!$this->currentUser->hasAnyAccess([$this->editPermission,$this->adminPermission]))
        {
            return parent::forbidden();
        }

        /*
         * If not admin & not owner of form
         */
        if(!$this->isAdmin && !$pr->isOwner())
        {
            return parent::forbidden();
        }

        if($form)
        {
            $v = \Input::get('value');
            //Validation

            switch(\Input::get('name'))
            {
                case 'jde_itm':
                    if(!is_numeric($v) || $v === "")
                    {
                        return \Response::make("Please select a valid product",500);
                    }
                    else
                    {
                        if(!\JdeProduct::find(Input::get('value')))
                        {
                            return \Response::make("Please select an existing product",500);
                        }
                    }
                    break;
                case 'pickup':
                    if($v === "" || !is_numeric($v))
                    {
                        return \Response::make("Please select a valid pickup option",500);
                    }
                    else
                    {
                        if(!in_array($v,[0,1]))
                        {
                            return \Response::make("Please select a valid pickup value",500);
                        }
                    }
                    break;
                case 'reason_id':
                    if($v === "" || !is_numeric($v))
                    {
                        return \Response::make("Please select a valid reason code",500);
                    }
                    else
                    {
                        if(!\SwiftPRReason::find($v))
                        {
                            return \Response::make("Please select an existing reason code",500);
                        }
                    }
                    break;
                case 'invoice_id':
                    if($v === "" || !is_numeric($v))
                    {
                        return \Response::make("Please enter a valid invoice number",500);
                    }
                    else
                    {
                        if($v < 0)
                        {
                            return \Response::make("Please enter a positive value",500);
                        }
                    }
                    break;
                case 'qty_client':
                case 'qty_pickup':
                case 'qty_store':
                case 'qty_triage_picking':
                case 'qty_triage_disposal':
                    if($v === "" || !is_numeric($v))
                    {
                        return \Response::make("Please enter a valid quantity",500);
                    }
                    else
                    {
                        if($v < 0)
                        {
                            return \Response::make("Please enter a positive value",500);
                        }
                    }
                    break;
                default:
                    return \Response::make("Unknown field",500);
                    break;
            }

            /*
             * New Product
             */
            if(is_numeric(Input::get('pk')))
            {
                //All Validation Passed, let's save
                $p = new \SwiftPRProduct();
                $p->{\Input::get('name')} = \Input::get('value') == "" ? null : $v;
                if($form->product()->save($p))
                {
                    switch(\Input::get('name'))
                    {
                        case 'jde_itm':
                            \Queue::push('Helper@getProductPrice',array('product_id'=>$p->id,'class'=>get_class($p)));
                            break;
                    }
                    return \Response::make(json_encode(['encrypted_id'=>\Crypt::encrypt($p->id),'id'=>$p->id]));
                }
                else
                {
                    return \Response::make('Failed to save. Please retry',400);
                }

            }
            else
            {
                $p = \SwiftPRProduct::find(\Crypt::decrypt(\Input::get('pk')));
                if($p)
                {
                    switch(\Input::get('name'))
                    {
                        case 'jde_itm':
                            \Queue::push('Helper@getProductPrice',array('product_id'=>$p->id,'class'=>get_class($p)));
                            break;
                    }

                    $p->{\Input::get('name')} = \Input::get('value') == "" ? null : \Input::get('value');
                    if($p->save())
                    {
                        return \Response::make('Success');
                    }
                    else
                    {
                        return \Response::make('Failed to save. Please retry',400);
                    }
                }
                else
                {
                    return \Response::make('Error saving product: Invalid PK',400);
                }
            }
            
        }

        return \Response::make("Form not found",500);
    }

    /*
     * Approval of products for Retail Man
     *
     * @param int $type
     * @param string $product_id
     *
     * @return \Illuminate\Support\Facades\Response
     */
    public function putProductapproval($type,$product_id)
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
            if(Input::get('name') == "approval_approved" && in_array(\Input::get('value'),array(\SwiftApproval::REJECTED,\SwiftApproval::APPROVED,\SwiftApproval::PENDING)))
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
                            $approval = new \SwiftApproval(array('type'=>(int)$type,'approval_user_id'=>$this->currentUser->id, 'approved' => Input::get('value')));
                            if($product->approval()->save($approval))
                            {
                                $pr = $product->pr()->first();
                                \Queue::push('WorkflowActivity@updateTask',array('class'=>get_class($pr),'id'=>$pr->id,'user_id'=>$this->currentUser->id));
                                return Response::make(json_encode(['encrypted_id'=>Crypt::encrypt($approval->id),'id'=>$approval->id]));
                            }
                            else
                            {
                                return Response::make('Failed to save. Please retry',400);
                            }

                        }
                        else
                        {
                            $approval = SwiftApproval::find(Crypt::decrypt(Input::get('pk')));
                            if(count($approval))
                            {
                                $approval->approved = Input::get('value') == "" ? null : Input::get('value');
                                if($approval->save())
                                {
                                    $pr = $product->pr()->first();
                                    Queue::push('WorkflowActivity@updateTask',array('class'=>get_class($pr),'id'=>$pr->id,'user_id'=>$this->currentUser->id));
                                    return Response::make('Success');
                                }
                                else
                                {
                                    return Response::make('Failed to save. Please retry',400);
                                }
                            }
                            else
                            {
                                return Response::make('Error saving approval information: Invalid PK',400);
                            }
                        }
                        break;
                    default:
                        return Response::make('Type of approval unknown',400);
                        break;
                }
            }
            else
            {
                return Response::make('Invalid Request',400);
            }
        }
        else
        {
            return Response::make('Product not found',404);
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

        $product = SwiftPRProduct::find(Crypt::decrypt($product_id));

        if(count($product))
        {
            if(Input::get('name') == "approval_comment")
            {
                switch((int)$type)
                {
                    case \SwiftApproval::PR_RETAILMAN:
                        if(is_numeric(Input::get('pk')))
                        {
                            return Response::make('Please approve the product first',400);
                        }
                        else
                        {
                            $approval = \SwiftApproval::find(Crypt::decrypt(Input::get('pk')));
                            if(count($approval))
                            {
                                if($approval->approved == SwiftApproval::REJECTED && trim(Input::get('value'))=="")
                                {
                                    return Response::make('Please enter a comment for rejected product',400);
                                }

                                //Get Comments
                                $comment = $approval->comments()->first();

                                if(count($comment))
                                {
                                    $comment->comment = trim(Input::get('value'));
                                    if($comment->save())
                                    {
                                        return Response::make('Success');
                                    }
                                }
                                else
                                {
                                    $newcomment = new SwiftComment(['comment'=>trim(Input::get('value')),'user_id'=>$this->currentUser->id]);
                                    if($approval->comments()->save($newcomment))
                                    {
                                        return Response::make('Success');
                                    }
                                }
                                return Response::make('Failed to save. Please retry',400);
                            }
                            else
                            {
                                return Response::make('Error saving approval comment: Invalid PK',400);
                            }
                        }
                        break;
                    default:
                        return Response::make('Type of approval unknown',400);
                        break;
                }
            }
            else
            {
                return Response::make('Invalid Request',400);
            }
        }
        else
        {
            return Response::make('Product not found',404);
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

                $qty_included = \Input::has('quantity_included');

                $invoice_lines = \JdeSales::getProducts(\Input::get('invoice_id'));
                
                foreach($products as $line_number => $jde_itm)
                {
                    //Check if Valid Product ITM
                    if(is_numeric($jde_itm) && \JdeProduct::find($jde_itm))
                    {
                        $qty_client = $price = null;
                        $filter = $invoice_lines->filter(function($line) use ($line_number){
                                                return (int)$line->LNID === (int)$line_number;
                                            })->first();
                        if($filter)
                        {
                            if($qty_included)
                            {
                                $qty_client = $filter->SOQS;
                            }
                            $price = $filter->AEXP/$filter->SOQS;
                        }

                        //Save Product Relationship
                        $pr->product()->save(
                            new SwiftPRProduct([
                                'jde_itm' => $jde_itm,
                                'pickup' => ($pr->type === \SwiftPR::SALESMAN ? 1 : 0),
                                'qty_client' => $qty_client,
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
                $form->revisionHistory()->orderBy('created_at','ASC')->first()->user_id != $this->currentUser->id)
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
    