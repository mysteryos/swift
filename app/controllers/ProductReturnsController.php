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
        $this->permission = $this->data['permission'] = new \Permission\SwiftPR();
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
        $this->pageTitle = 'Overview';
        $this->data['inprogress_limit'] = 15;
        $this->data['late_node_forms_count'] = \SwiftNodeActivity::countLateNodes($this->context);
        $this->data['pending_node_count'] = \SwiftNodeActivity::countPendingNodesWithEta($this->context);

        /*
         * Fetch all data for 'Workspot' box
         */

        $pr_inprogress = $pr_inprogress_important = $pr_inprogress_responsible = $pr_inprogress_important_responsible = array();

        $pr_inprogress = \SwiftPR::getInProgress($this->data['inprogress_limit']);
        $pr_inprogress_count = \SwiftPR::getInProgressCount();
        $pr_inprogress_important = \SwiftPR::getInProgress(0,true);
        $pr_inprogress_responsible = \SwiftPR::getInProgressResponsible();
        $pr_inprogress_important_responsible = \SwiftPR::getInProgressResponsible(0,true);

        $pr_inprogress = $pr_inprogress->diff($pr_inprogress_responsible);
        $pr_inprogress_important = $pr_inprogress_important->diff($pr_inprogress_important_responsible);

        /*
         * Check if we have in progress data
         */
        if(count($pr_inprogress) == 0 || count($pr_inprogress_important) == 0 || count($pr_inprogress_responsible) == 0 || count($pr_inprogress_important_responsible) == 0)
        {
            $this->data['in_progress_present'] = true;
        }
        else
        {
            $this->data['in_progress_present'] = false;
        }

        /*
         * Add workflowactivity and audit to each record.
         */
        foreach(array($pr_inprogress,$pr_inprogress_responsible,$pr_inprogress_important,$pr_inprogress_important_responsible) as $prarray)
        {
            foreach($prarray as &$pr)
            {
                $pr->current_activity = \WorkflowActivity::progress($pr);
                $pr->activity = \Helper::getMergedRevision($pr->revisionRelations,$pr);
            }
        }

        $this->data['inprogress'] = $pr_inprogress;
        $this->data['inprogress_responsible'] = $pr_inprogress_responsible;
        $this->data['inprogress_important'] = $pr_inprogress_important;
        $this->data['inprogress_important_responsible'] = $pr_inprogress_important_responsible;
        $this->adminList();

        return $this->makeView('product-returns/overview');
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
        $this->task()->registerFormFilters();

        /*
         * Filter Lists
         */
        $this->data['filter_list_owners'] = $this->task()->getListOwners();
        $this->data['filter_list_step'] = $this->task()->getListStep();
        $this->data['filter_list_drivers'] = $this->task()->getListDrivers();
        $this->data['filter_list_customers'] = $this->task()->getListCustomers();

        //Check user group
        if($type===false)
        {
            if(!$this->currentUser->isSuperUser() && $this->permission->isSalesman())
            {
                $type = 'mine';
            }
            else
            {
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
            case 'mine':
                $prquery->orderBy('updated_at','DESC')
                        ->where('owner_user_id','=',$this->currentUser->id);
                break;
            case 'all':
                $prquery->orderBy('updated_at','desc');
                break;
        }

        //The Filters
        $this->task()->applyFilters($prquery);

        $formsCount = $prquery->count();

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
        $this->data['count'] = $formsCount;
        $this->data['type'] = $type;
        $this->data['page'] = $page;
        $this->data['limit_per_page'] = $limitPerPage;
        $this->data['filter_string'] = "?".$_SERVER['QUERY_STRING'];
        $this->data['filter'] = $this->filter;
        $this->data['filter_on'] = (boolean)count(array_filter($this->filter,function($v){
                                        return $v['enabled'];
                                    }));
        $this->data['total_pages'] = ceil($this->data['count']/$limitPerPage);
        $this->data['pageTitle'] = "Forms - ".ucfirst($type);

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
                                            $this->data['publishStoreValidation'] =
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
                        \WorkflowActivity::update($pr);
                        $this->data['current_activity'] = \WorkflowActivity::progress($pr,$this->context);
                    }

                    //Set the controls

                    foreach($this->data['current_activity']['definition_obj'] as $d)
                    {
                        if($d->data != "")
                        {
                            //Add Product
                            if(isset($d->data->publishOwner) &&
                                !$pr->approval()->where('type','=',\SwiftApproval::PR_REQUESTER)->count() &&
                                ($this->permission->isAdmin() || $pr->isOwner()))
                            {
                                $this->data['publishOwner'] = true;
                                if(isset($d->data->addProduct) && $pr->isOwner())
                                {
                                    $this->data['addProduct'] = true;
                                }
                                break;
                            }

                            //Store Pickup
                            if(isset($d->data->publishPickup) && ($this->permission->isAdmin() || $this->permission->isStorePickup()))
                            {
                                $this->data['publishPickup'] = true;
                                break;
                            }

                            //Store Reception
                            if(isset($d->data->publishReception) && ($this->permission->isAdmin() || $this->permission->isStoreReception()))
                            {
                                $this->data['publishReception'] = true;
                                break;
                            }

                            //Store Validation
                            if(isset($d->data->publishStoreValidation) && ($this->permission->isAdmin() || $this->permission->isStoreValidation()))
                            {
                                $this->data['publishStoreValidation'] = true;
                            }

                            //Credit Note
                            if(isset($d->data->publishCreditNote) && ($this->permission->isAdmin() || $this->permission->isCreditor()))
                            {
                                $this->data['publishCreditNote'] = true;
                                break;
                            }

                            //Driver Information
                            if(isset($d->data->driverInfo) && ($this->permission->isAdmin() || $this->permission->isStorePickup()))
                            {
                                $this->data['driverInfo'] = true;
                                break;
                            }
                        }
                    }

                    //Admins can edit products anytime
                    if($this->permission->isAdmin())
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
    public function getView($id,$override=false)
    {
        if($override === true)
        {
            return $this->form($id,false);
        }

        if($this->permission->canEdit() || $this->permission->isAdmin())
        {
            return \Redirect::action('ProductReturnsController@getEdit',array('id'=>$id));
        }
        elseif($this->permission->canView())
        {
            return $this->form($id,false);
        }
        else
        {
            /*
             * Check Sharing Settings
             */

            $className = \Config::get('context.'.$this->context);
            //Check Sharing Settings
            $sharedUser = \SwiftShare::findUserByForm($className,\Crypt::decrypt($id),$this->currentUser->id);

            if($sharedUser)
            {
                //Check Permission
                if($sharedUser->permission === \SwiftShare::PERMISSION_EDIT)
                {
                    return \Redirect::action('ProductReturnsController@getEdit',array('id'=>$id,'override'=>true));
                }
                else
                {
                    return $this->form($id,false);
                }
            }
            else
            {
                return parent::forbidden();
            }
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
        if($this->currentUser->hasAnyAccess([$this->permission->editPermission,$this->permission->adminPermission]))
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
    public function getCreate($type = \SwiftPR::SALESMAN)
    {
        if(!$this->permission->canCreate())
        {
            return parent::forbidden();
        }

        if(!in_array($type,[\SwiftPR::ON_DELIVERY,\SwiftPR::SALESMAN,\SwiftPR::INVOICE_CANCELLED]))
        {
            return parent::notfound();
        }

        $this->data['type'] = $type;
        $this->data['type_name'] = \SwiftPR::$type[$type];

        /*
         * Permissions
         */
        switch($type)
        {
            case \SwiftPR::SALESMAN:
                if(!$this->permission->canCreateSalesman())
                {
                    return parent::forbidden();
                }
                $this->data['product_reason_codes_array'] = \SwiftPRReason::getAll();
                $this->pageTitle = 'Create';
                return $this->makeView("$this->context/create");
                break;
            case \SwiftPR::ON_DELIVERY:
                if(!$this->permission->canCreateOnDelivery())
                {
                    return parent::forbidden();
                }
                $this->pageTitle = 'Create - On Delivery';
                $this->data['product_reason_codes_array'] = \SwiftPRReason::getAll();
                $this->data['driver_list_array'] = \SwiftDriver::getAll();
                return $this->makeView("$this->context/create_ondelivery");
                break;
            case \SwiftPR::INVOICE_CANCELLED:
                if(!$this->permission->canCreateInvoiceCancelled())
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
    }

    /*
     * Display Pending Tasks
     *
     * @param string $type
     * @return \Illuminate\Support\Facades\Response
     */
    public function getTasks($type='all')
    {
        $this->data['type'] = $type;

        return $this->task()->tasker($type);
    }

    /*
     * Statistics
     *
     * @return \Illuminate\Support\Facades\Response
     */
    public function getStatistics()
    {
        $startDate = \Helper::getFinancialYearStart();
        $endDate = \Helper::getFinancialYearEnd();

        $this->data['accepted_sum_selling_price'] = \SwiftPRProduct::statsByProductsAccepted($startDate,$endDate);
//        $this->data['accepted_sum_qty'] = ;
//        $this->data['rejected_sum_selling_price'] =;
//        $this->data['rejected_sum_qty'] = ;
    }

    /*
     * Save new invoice cancelled Form
     *
     * @param int $type
     * @return \Illuminate\Support\Facades\Response
     */

    public function postCreate()
    {
        //Basic Permission check
        if(!$this->permission->canCreate())
        {
            return parent::forbidden();
        }

        return $this->process()->create();
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

        return $this->process()->createInvoiceCancelled();
    }

    /*
     * POST: Publish Owner
     *
     * @param string $form_id
     * @return \Illuminate\Support\Facades\Response
     */

    public function postPublishOwner($form_id)
    {
        /*
         * Check Permissions
         */
        if(!$this->currentUser->hasAnyAccess([$this->permission->adminPermission,$this->permission->editPermission]))
        {
            return parent::forbidden();
        }

        $publishResult = $this->process()->publish(\Crypt::decrypt($form_id),\SwiftApproval::PR_REQUESTER);
        if($publishResult === true)
        {
            return \Response::make('Success');
        }
        else
        {
            return $publishResult;
        }
    }

    public function postPublishPickup($form_id)
    {
        /*
         * Check Permissions
         */
        if(!$this->currentUser->hasAnyAccess([$this->permission->adminPermission,$this->permission->editPermission]))
        {
            return parent::forbidden();
        }

        $publishResult = $this->process()->publish(\Crypt::decrypt($form_id),\SwiftApproval::PR_PICKUP);
        if($publishResult === true)
        {
            return \Response::make('Success');
        }
        else
        {
            return $publishResult;
        }
    }

    public function postPublishReception($form_id)
    {
        /*
         * Check Permissions
         */
        if(!$this->currentUser->hasAnyAccess([$this->permission->adminPermission,$this->permission->editPermission]))
        {
            return parent::forbidden();
        }

        $publishResult = $this->process()->publish(\Crypt::decrypt($form_id),\SwiftApproval::PR_RECEPTION);
        if($publishResult === true)
        {
            return \Response::make('Success');
        }
        else
        {
            return $publishResult;
        }
    }

    public function postPublishStoreValidation($form_id)
    {
        /*
         * Check Permissions
         */
        if(!$this->currentUser->hasAnyAccess([$this->permission->adminPermission,$this->permission->editPermission]))
        {
            return parent::forbidden();
        }

        $publishResult = $this->process()->publish(\Crypt::decrypt($form_id),\SwiftApproval::PR_STOREVALIDATION);
        if($publishResult === true)
        {
            return \Response::make('Success');
        }
        else
        {
            return $publishResult;
        }
    }

    public function postPublishCreditNote($form_id)
    {
        /*
         * Check Permissions
         */
        if(!$this->currentUser->hasAnyAccess([$this->permission->adminPermission,$this->permission->editPermission]))
        {
            return parent::forbidden();
        }

        $publishResult = $this->process()->publish(\Crypt::decrypt($form_id),\SwiftApproval::PR_CREDITNOTE);
        if($publishResult === true)
        {
            return \Response::make('Success');
        }
        else
        {
            return $publishResult;
        }

    }

    /*
     * PUT: General Info
     *
     * @param string $form_id
     * @return \Illuminate\Support\Facades\Response
     */

    public function putGeneralinfo()
    {
        return $this->process()->save(\Input::get('pk'));
    }

    /*
     * PUT: Product
     *
     * @return \Illuminate\Support\Facades\Response
     */

    public function putProduct($form_id)
    {
        return $this->process('SwiftPRProduct')->save($form_id);
    }

    /*
     * DELETE: Product
     *
     * @return \Illuminate\Support\Facades\Response
     */
    public function deleteProduct()
    {
        return $this->process('SwiftPRProduct')->delete();
    }

    /*
     * PUT: JDE Order
     *
     * @param string $pr_id
     * @return \Illuminate\Support\Facades\Response
     */
    public function putErporder($pr_id)
    {
        /*
         * Check Permissions
         */
        if(!$this->permission->isAdmin() && !$this->permission->isCcare())
        {
            return parent::forbidden();
        }

        return $this->process('SwiftErpOrder')
                    ->saveByParent(\Crypt::decrypt($pr_id));

    }

    /*
     * DELETE: JDE Order
     *
     * @return \Illuminate\Support\Facades\Response
     */
    public function deleteErporder()
    {
        return $this->process('SwiftErpOrder')->delete();
    }

    /*
     * PUT: Credit Note
     *
     * @param string $pr_id
     * @return \Illuminate\Support\Facades\Response
     */
    public function putCreditNote($pr_id)
    {
        /*
         * Check Permissions
         */
        if(!$this->permission->isAdmin() && !$this->permission->isCreditor())
        {
            return parent::forbidden();
        }

        return $this->process('SwiftCreditNote')
                    ->saveByParent(\Crypt::decrypt($pr_id));

    }

    /*
     * DELETE: Credit Note
     *
     * @return \Illuminate\Support\Facades\Response
     */
    public function deleteCreditNote()
    {
        return $this->process('SwiftCreditNote')->delete();
    }

    /*
     * PUT: JDE Order
     *
     * @param string $pr_id
     * @return \Illuminate\Support\Facades\Response
     */
    public function putPickup($pr_id)
    {
        /*
         * Check Permissions
         */
        if(!$this->permission->isAdmin() && !$this->permission->isStorePickup())
        {
            return parent::forbidden();
        }

        return $this->process('SwiftPickup')
                    ->saveByParent(\Crypt::decrypt($pr_id));

    }

    /*
     * DELETE: JDE Order
     *
     * @return \Illuminate\Support\Facades\Response
     */
    public function deletePickup()
    {
        return $this->process('SwiftPickup')->delete();
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
        if(!$this->permission->isRetailMan())
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
        if(!$this->permission->isRetailMan())
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

                                if($comment)
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
        if(!$this->currentUser->hasAnyAccess([$this->permission->adminPermission,$this->permission->editPermission]))
        {
            return parent::forbidden();
        }

        return $this->process('SwiftDocument')
                    ->setParentResource(new \SwiftPRDocument)
                    ->upload('SwiftPR',\Crypt::decrypt($pr_id));
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
        if(!$this->currentUser->hasAnyAccess([$this->permission->adminPermission,$this->permission->editPermission]))
        {
            return parent::forbidden();
        }

        return $this->process('SwiftDocument')
                    ->setParentResource(new \SwiftPRDocument)
                    ->delete(\Crypt::decrypt($doc_id));
    }

    /*
     * Tags: REST
     */

    public function putTag()
    {

        return $this->process('SwiftTag')
                    ->setParentResource(new \SwiftPRDocument)
                    ->save(\SwiftTag::$prTags);
    }

    /*
     * Cancel Form
     */

    public function postCancel($pr_id)
    {
        /*
         * Check Permissions
         */
        if(!$this->currentUser->hasAccess([$this->permission->adminPermission,$this->permission->editPermission]))
        {
            return parent::forbidden();
        }

        return $this->process('SwiftWorkflowActivity')
                ->cancel('SwiftPR',\Crypt::decrypt($pr_id),function($process){
                    return  $process->controller->currentUser->hasAccess($process->controller->editPermission) &&
                            !$process->controller->currentUser->isSuperUser() &&
                            !$process->form->isOwner();
                });
    }

    public function getPrintPickup($form_id)
    {
        /*
         * Check Permissions
         */
        if(!$this->currentUser->hasAnyAccess([$this->permission->adminPermission,$this->permission->editPermission]))
        {
            return parent::forbidden();
        }

        return $this->process()->generatePdf([\Crypt::decrypt($form_id)]);
    }

    public function getCreditNoteExcel()
    {
        /*
         * Check Permissions
         */

        if(!$this->permission->isCreditor() && !$this->permission->isAdmin())
        {
            return parent::forbidden();
        }

        return $this->process()->generateCreditNoteExcel();
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
        if(!$this->currentUser->hasAnyAccess([$this->permission->adminPermission,$this->permission->editPermission]))
        {
            return "You don't have access to this resource.";
        }

        $needPermission = true;

        if($this->currentUser->hasAccess($this->permission->adminPermission))
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
                                            $this->data['publishStoreValidation'] =
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
                    \WorkflowActivity::update($pr);
                }

                foreach($this->data['current_activity']['definition_obj'] as $d)
                {
                    if($d->data != "")
                    {
                        if(isset($d->data->publishOwner) && ($this->permission->isAdmin() || $pr->isOwner()))
                        {
                            $this->data['publishOwner'] = true;
                            if(isset($d->data->addProduct) && ($pr->isOwner() || $this->permission->isAdmin()))
                            {
                                $this->data['addProduct'] = true;
                            }
                            break;
                        }

                        if(isset($d->data->publishPickup) && ($this->permission->isAdmin() || $this->permission->isStorePickup()))
                        {
                            $this->data['publishPickup'] = true;
                            break;
                        }

                        if(isset($d->data->publishReception) && ($this->permission->isAdmin() || $this->permission->isStoreReception()))
                        {
                            $this->data['publishReception'] = true;
                            break;
                        }

                        //Store Validation
                        if(isset($d->data->publishStoreValidation) && ($this->permission->isAdmin() || $this->permission->isStoreValidation()))
                        {
                            $this->data['publishStoreValidation'] = true;
                        }

                        //Credit Note
                        if(isset($d->data->publishCreditNote) && ($this->permission->isAdmin() || $this->permission->isCreditor()))
                        {
                            $this->data['publishCreditNote'] = true;
                            break;
                        }

                        //Driver Information
                        if(isset($d->data->driverInfo) && ($this->permission->isAdmin() || $this->permission->isStorePickup()))
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
     * Get List of my Product return Requests - HTML
     *
     */
    public function getMyrequests()
    {
        $this->data['pending_requests'] = \SwiftPR::getMyPending();
        $this->data['complete_requests'] = \SwiftPR::getMyCompleted(5);

        if(!$this->data['pending_requests']->isEmpty() || !$this->data['complete_requests']->isEmpty())
        {
            $this->data['requests_present'] = true;
            foreach(array($this->data['pending_requests'],$this->data['complete_requests']) as $prsource)
            {
                foreach($prsource as &$pr)
                {
                    $pr->current_activity = \WorkflowActivity::progress($pr,$this->context);
                    $pr->activity = \Helper::getMergedRevision($pr->revisionRelations,$pr);
                }
            }
        }
        else
        {
            $this->data['requests_present'] = false;
        }

        echo View::make('product-returns/overview_myrequests',$this->data)->render();
    }

    /*
     * Overview: AJAX Widgets
     */
    public function getLateNodes()
    {
        if($this->permission->isAdmin())
        {
            $this->data['late_node_forms_count'] = SwiftNodeActivity::countLateNodes($this->context);
            if($this->data['late_node_forms_count'] <= 50) {
                $this->data['too_many_late_nodes'] = false;
                $this->data['late_node_forms'] = WorkflowActivity::lateNodeByForm($this->context);
            } else {
                $this->data['too_many_late_nodes'] = true;
            }

            echo View::make('workflow/overview_latenodes',$this->data)->render();
        }
        else
        {
            return parent::forbidden();
        }
    }

    public function getPendingNodes()
    {

        if($this->permission->isAdmin())
        {
            $this->data['pending_node_activity'] = \WorkflowActivity::statusByType($this->context);

            echo View::make('workflow/overview_pendingnodes',$this->data)->render();
        }
        else
        {
            return parent::forbidden();
        }
    }

    public function getStories()
    {
        if($this->permission->isAdmin())
        {
            $this->data['stories'] = \Story::fetch(\Config::get('context')[$this->context]);
            $this->data['dynamicStory'] = false;

            echo View::make('story/chapter',$this->data)->render();
        }
        else
        {
            return parent::forbidden();
        }
    }

    /*
     * AJAX CALLS: End
     */
}
