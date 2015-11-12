<?php

class APRequestController extends UserController {

    public function __construct(){
        parent::__construct();
        $this->pageName = "A&P Request";
        $this->rootURL = $this->data['rootURL'] = $this->context = $this->data['context'] = "aprequest";
        $this->permission = $this->data['permission'] = new \Permission\SwiftAPRequest();
    }

    public function getIndex()
    {
        //Send back to overview
        return Redirect::to('/'.$this->context.'/overview');
    }

    /*
     * Overview
     */

    public function getOverview()
    {
        $this->pageTitle = 'Overview';
        $this->data['inprogress_limit'] = 15;
        $this->data['late_node_forms_count'] = SwiftNodeActivity::countLateNodes($this->context);
        $this->data['pending_node_count'] = SwiftNodeActivity::countPendingNodesWithEta($this->context);

        /*
         * Fetch all data for 'Workspot' box
         */

        $aprequest_inprogress = $aprequest_inprogress_important = $aprequest_inprogress_responsible = $aprequest_inprogress_important_responsible = array();

        $aprequest_inprogress = \SwiftAPRequest::getInProgress($this->data['inprogress_limit']);
        $aprequest_inprogress_count = \SwiftAPRequest::getInProgressCount();
        $aprequest_inprogress_important = \SwiftAPRequest::getInProgress(0,true);
        $aprequest_inprogress_responsible = \SwiftAPRequest::getInProgressResponsible();
        $aprequest_inprogress_important_responsible = \SwiftAPRequest::getInProgressResponsible(0,true);

        $aprequest_inprogress = $aprequest_inprogress->diff($aprequest_inprogress_responsible);
        $aprequest_inprogress_important = $aprequest_inprogress_important->diff($aprequest_inprogress_important_responsible);

        /*
         * Check if we have in progress data
         */
        if(count($aprequest_inprogress) == 0 || count($aprequest_inprogress_important) == 0 || count($aprequest_inprogress_responsible) == 0 || count($aprequest_inprogress_important_responsible) == 0)
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
        foreach(array($aprequest_inprogress,$aprequest_inprogress_responsible,$aprequest_inprogress_important,$aprequest_inprogress_important_responsible) as $aprequestarray)
        {
            foreach($aprequestarray as &$apr)
            {
                $apr->current_activity = WorkflowActivity::progress($apr);
                $apr->activity = Helper::getMergedRevision($apr->revisionRelations,$apr);
            }
        }

        $this->data['inprogress'] = $aprequest_inprogress;
        $this->data['inprogress_responsible'] = $aprequest_inprogress_responsible;
        $this->data['inprogress_important'] = $aprequest_inprogress_important;
        $this->data['inprogress_important_responsible'] = $aprequest_inprogress_important_responsible;
        /*$this->data['aprequest_storage'] = $storage_array*/
        $this->data['isAdmin'] = $this->currentUser->hasAccess($this->permission->adminPermission);
        $this->adminList();

        return $this->makeView('aprequest/overview');

    }

    /*
     * Name: Form
     * Description: Fills in
     */
    private function form($id,$edit=false)
    {
        $apr_id = \Crypt::decrypt($id);
        $apr = \SwiftAPRequest::getById($apr_id);
        if(count($apr))
        {
            /*
             * Encrypted Id
             */

            $apr->encrypted_id = \Crypt::encrypt($apr->id);

            /*
             * Set Read
             */

            if(!\Flag::isRead($apr))
            {
                \Flag::toggleRead($apr);
            }

            /*
             * Enable Commenting
             */
            $this->enableComment($apr);

            /*
             * Enable Subscription
             */

            $this->enableSubscription($apr);

            /*
             * Data
             */
            $this->data['isCreator'] = ($this->currentUser->id === $apr->requester_user_id);
            //Workflow Activity
            $this->data['current_activity'] = \WorkflowActivity::progress($apr,$this->context);
            //Audit data
            $this->data['activity'] = \Helper::getMergedRevision(array('product','document','delivery','order','product.approvalexec','product.approvalcatman'),$apr);
            $this->pageTitle = "{$apr->name} (ID: $apr->id)";
            $this->data['form'] = $apr;
            /*
             * List data
             */
            $this->data['product_reason_code'] = json_encode(\Helper::jsonobject_encode(SwiftAPProduct::$reason));
            $this->data['approval_code'] = json_encode(\Helper::jsonobject_encode(SwiftApproval::$approved));
            $this->data['erporder_type'] = json_encode(\Helper::jsonobject_encode(SwiftErpOrder::$type));
            $this->data['erporder_status'] = json_encode(\Helper::jsonobject_encode(SwiftErpOrder::$status));
            $this->data['delivery_status'] = json_encode(\Helper::jsonobject_encode(SwiftDelivery::$status));
            $this->data['tags'] = json_encode(\Helper::jsonobject_encode(SwiftTag::$aprequestTags));
            /*
             * Flags
             */
            $this->data['flag_important'] = \Flag::isImportant($apr);
            $this->data['flag_starred'] = \Flag::isStarred($apr);

            /*
             * Edit Access
             */
            $this->data['canPublish'] = $this->data['canModifyProduct'] = $this->data['canAddProduct'] = false;
            $this->data['edit'] = $edit;
            $this->data['owner'] = \Helper::getUserName($apr->requester_user_id,$this->currentUser);

            /*
             * See if can publish
             */

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
                        \WorkflowActivity::update($apr);
                    }
                    else
                    {
                        foreach($this->data['current_activity']['definition_obj'] as $d)
                        {
                            if($d->data !== "" || $this->permission->isAdmin())
                            {
                                /*
                                 * Check permissions based on current nodes in workflow
                                 */
                                if($this->permission->isAdmin() === true || isset($d->data->addproduct))
                                {
                                    $this->data['canAddProduct'] = true;
                                }

                                if($this->permission->isAdmin() === true || (isset($d->data->modifyproduct) && $this->data['isCreator']))
                                {
                                    $this->data['canModifyProduct'] = true;
                                }

                                if(isset($d->data->manualpublish) &&
                                    !$apr->approval()->where('type','=',\SwiftApproval::APR_REQUESTER)->count() &&
                                    ($this->permission->isAdmin() || $this->data['isCreator']))
                                {
                                    $this->data['canPublish'] = true;
                                    break;
                                }
                            }
                        }
                    }
                }
            }

            /*
             * If there are products
             */
            if(count($apr->product))
            {
                $total = 0;
                foreach($apr->product as &$p)
                {
                    /*
                     * Check if approved
                     */
                    $p->approvalstatus = \SwiftApproval::PENDING;
                    if(count($p->approvalcatman) && $p->approvalcatman->approved != SwiftApproval::PENDING)
                    {
                        $p->approvalstatus = $p->approvalcatman->approved;
                    }

                    if(count($p->approvalexec) && $p->approvalexec->approved != SwiftApproval::PENDING)
                    {
                        $p->approvalstatus = $p->approvalexec->approved;
                    }

                    /*
                     * Calculate total
                     */
                    if((int)$p->quantity > 0 && (float)$p->price > 0)
                    {
                        $total += ($p->quantity * $p->price);
                    }
                }
                $this->data['product_price_total'] = round($total, 2);
            }

            //Save recently viewed form
            \Helper::saveRecent($apr,$this->currentUser);

            return $this->makeView("$this->rootURL/edit");
        }
        else
        {
            return parent::notfound();
        }
    }

    /*
     * GET Pages
     */
    public function getCreate()
    {
        //Check Permission for creating form
        if(NodeActivity::hasStartAccess($this->context))
        {
            $this->pageTitle = 'Create';
            return $this->makeView("$this->rootURL/create");
        }
        else
        {
            return parent::forbidden();
        }
    }

    /*
     * Display Form
     */
    public function getView($id,$override=false)
    {
        if($override === true)
        {
            return $this->form($id,false);
        }

        /*
         * If user has edit access, we redirect
         */
        if($this->currentUser->hasAnyAccess([$this->permission->editPermission,$this->permission->adminPermission]))
        {
            return Redirect::action('APRequestController@getEdit',array('id'=>$id));
        }
        elseif($this->currentUser->hasAnyAccess([$this->permission->viewPermission]))
        {
            return $this->form($id,false);
        }
        else
        {
            //No permission - Forbidden
            return parent::forbidden();
        }
    }

    /*
     * Edit Form
     */
    public function getEdit($id)
    {
        if($this->currentUser->hasAnyAccess([$this->permission->editPermission,$this->permission->adminPermission]))
        {
            return $this->form($id,true);
        }
        elseif($this->currentUser->hasAnyAccess([$this->permission->viewPermission]))
        {
            /*
             * Check if has view access
             */
            return Redirect::action('APRequestController@getView',array('id'=>$id));
        }
        else
        {
            //No permission - Forbidden
            return parent::forbidden();
        }
    }

    /*
     * Lists all forms
     */
    public function getForms($type='all',$page=1)
    {
        $limitPerPage = 15;

        $this->pageTitle = 'Forms';

        //Check Edit Access
        $this->data['edit_access'] = $this->currentUser->hasAnyAccess([$this->permission->editPermission,$this->permission->adminPermission]);

        //Check user group
        if(!$this->data['edit_access'] && $type='inprogress')
        {
            $type='all';
        }

        $aprequestquery = SwiftAPRequest::query();

        /*
         * Get filter for node definition if not inprogress
         */
        if($type != 'inprogress')
        {
            //Get node definition list
            $node_definition_result = SwiftNodeDefinition::getByWorkflowType(SwiftWorkflowType::where('name','=',$this->context)->first()->id)->all();
            $node_definition_list = array();
            foreach($node_definition_result as $v)
            {
                $node_definition_list[$v->id] = $v->label;
            }
            $this->data['node_definition_list'] = $node_definition_list;
        }

        /*
         * Category Filter
         */
        switch($type)
        {
            case 'inprogress':
                $aprequestquery->whereHas('workflow',function($q){
                    return $q->where('status','=',SwiftWorkflowActivity::INPROGRESS);
                });
                break;
            case 'rejected':
                $aprequestquery->whereHas('workflow',function($q){
                   return $q->where('status','=',SwiftWorkflowActivity::REJECTED);
                });
                break;
            case 'completed':
                $aprequestquery->whereHas('workflow',function($q){
                   return $q->where('status','=',SwiftWorkflowActivity::COMPLETE);
                });
                break;
            case 'starred':
                $aprequestquery->whereHas('flag',function($q){
                   return $q->where('type','=',SwiftFlag::STARRED,'AND')->where('user_id','=',$this->currentUser->id,'AND')->where('active','=',SwiftFlag::ACTIVE);
                });
                break;
            case 'important':
                $aprequestquery->whereHas('flag',function($q){
                   return $q->where('type','=',SwiftFlag::IMPORTANT,'AND');
                });
                break;
            case 'mine':
                $aprequestquery->where('requester_user_id','=',$this->currentUser->id);
                break;
            case 'all':
                $aprequestquery->orderBy('updated_at','desc');
                break;
        }

        //Set filters
        if(\Input::has('filter'))
        {
            //Retrieve from session if possible
            if(\Session::has('apr_form_filter'))
            {
                $filter = Session::get('apr_form_filter');
            }
            else
            {
                $filter = array();
            }

            $filter[Input::get('filter_name')] = Input::get('filter_value');

            /*
             * loop & Apply all filters
             */
            foreach($filter as $f_name => $f_val)
            {
                switch($f_name)
                {
                    case 'business_unit':
                        $aprequestquery->where('business_unit','=',$f_val);
                        break;
                    case 'node_definition_id':
                        $aprequestquery->whereHas('workflow',function($q) use($f_val){
                           return $q->whereHas('nodes',function($q) use($f_val){
                               return $q->where('node_definition_id','=',$f_val);
                           });
                        });
                        break;
                }
            }

            Session::flash('apr_form_filter',$filter);

        }
        else
        {
            Session::forget('apr_form_filter');
        }

        $formsCount = $aprequestquery->count();
        if($type != 'inprogress')
        {
            /*
             * If not in progress, we limit rows
             */
            $aprequestquery->take($limitPerPage);
            if($page > 1)
            {
                $aprequestquery->offset(($page-1)*$limitPerPage);
            }
        }
        $forms = $aprequestquery->get();

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
            else
            {
                if(isset($filter) && isset($filter['node_definition_id']))
                {
                    if(!isset($f->current_activity['definition']) || !in_array((int)$filter['node_definition_id'],$f->current_activity['definition']))
                    {
                        unset($forms[$k]);
                        $formsCount--;
                        break;
                    }
                }
            }

            //Set Revision
            $f->revision_latest = Helper::getMergedRevision(array('product'),$f);

            //Set Starred/important
            $f->flag_starred = Flag::isStarred($f);
            $f->flag_important = Flag::isImportant($f);
            $f->flag_read = Flag::isRead($f);
        }

        //The Data
        $this->data['type'] = $type;
        $this->data['forms'] = $forms;
        $this->data['count'] = isset($filter) ? $formsCount : SwiftAPRequest::count();
        $this->data['page'] = $page;
        $this->data['limit_per_page'] = $limitPerPage;
        $this->data['total_pages'] = ceil($this->data['count']/$limitPerPage);
        $this->data['filter'] = Input::has('filter') ? "?filter=1" : "";

        return $this->makeView("$this->rootURL/forms");
    }

    /*
     * Approvals
     */

    public function getApproval()
    {
        if(!$this->currentUser->hasAnyAccess(['apr-catman','apr-exec']))
        {
            return parent::forbidden();
        }

        $this->pageTitle = "Approval";

        $forms = \SwiftAPRequest::getInProgressResponsibleWithProducts();
        $this->data['hasForms'] = (boolean)count($forms);
        $this->data['today_forms'] = $this->data['yesterday_forms'] = array();

        foreach($forms as $key => &$f)
        {
            $f->current_activity = \WorkflowActivity::progress($f,$this->context);
            $f->approval_level = \SwiftApproval::APR_CATMAN;
            $f->encrypted_id = \Crypt::encrypt($f->id);
            foreach($f->current_activity['definition_obj'] as $d)
            {
                if($d->name === "apr_execapproval")
                {
                    $f->approval_level = \SwiftApproval::APR_EXEC;
                    break;
                }
            }

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

        $this->data['forms'] = $forms;

        return $this->makeView("$this->rootURL/approval");
    }

    /*
     * POST Create Form
     */

    public function postCreate()
    {
        /*
         * Check Permission
         */
        if(!$this->currentUser->hasAccess($this->permission->editPermission) || !NodeActivity::hasStartAccess($this->context))
        {
            return parent::forbidden();
        }

        $validator = Validator::make(Input::all(),
                        array('name'=>'required',
                              'customer_code'=>'required')
                    );

        if($validator->fails())
        {
            return json_encode(['success'=>0,'errors'=>$validator->errors()]);
        }
        else
        {
            $aprequest = new SwiftAPRequest(Input::All());
            $aprequest->requester_user_id = $this->currentUser->id;
            if($aprequest->save())
            {
                //Start the Workflow
                if(\WorkflowActivity::update($aprequest,$this->context))
                {
                    //Story Relate
                    Queue::push('Story@relateTask',array('obj_class'=>get_class($aprequest),
                                                         'obj_id'=>$aprequest->id,
                                                         'action'=>SwiftStory::ACTION_CREATE,
                                                         'user_id'=>$this->currentUser->id,
                                                         'context'=>get_class($aprequest)));
                    //Success
                    echo json_encode(['success'=>1,'url'=>Helper::generateUrl($aprequest)]);
                }
                else
                {
                    return Response::make("Failed to save workflow",400);
                }
            }
            else
            {
                echo "";
                return false;
            }
        }
    }

    /*
     * General Info: REST
     */
    public function putGeneralinfo()
    {
        /*
         * Check Permissions
         */
        if(!$this->currentUser->hasAnyAccess([$this->permission->adminPermission,$this->permission->editPermission]))
        {
            return parent::forbidden();
        }

        $aprequest_id = Crypt::decrypt(Input::get('pk'));
        $aprequest = SwiftAPRequest::find($aprequest_id);
        if(count($aprequest))
        {
            /*
             * Manual Validation
             */

            //Name
            if(\Input::get('name') == 'name' && trim(\Input::get('value')==""))
            {
                return \Response::make('Please enter a name',400);
            }

             if(\Input::get('name') == 'customer_code' && trim(\Input::get('value')==""))
            {
                return \Response::make('Please enter a customer name',400);
            }

            /*
             * Save
             */
            $aprequest->{\Input::get('name')} = \Input::get('value') == "" ? null : \Input::get('value');
            if($aprequest->save())
            {
                \Queue::push('WorkflowActivity@updateTask',array('class'=>get_class($aprequest),'id'=>$aprequest->id,'user_id'=>$this->currentUser->id));
                return \Response::make('Success', 200);
            }
            else
            {
                return \Response::make('Failed to save. Please retry',400);
            }
        }
        else
        {
            return \Response::make('A&P Request form not found',404);
        }
    }

    public function putProduct($apr_id)
    {
        /*
         * Check Permissions
         */
        if(!$this->currentUser->hasAnyAccess([$this->permission->adminPermission,$this->permission->editPermission]))
        {
            return parent::forbidden();
        }

        $form = \SwiftAPRequest::find(Crypt::decrypt($apr_id));

        /*
         * Manual Validation
         */
        if(count($form))
        {
            switch(\Input::get('name'))
            {
                case 'status':
                    if(!array_key_exists(\Input::get('value'),\SwiftAPProduct::$reason))
                    {
                        return \Response::make('Please select a valid reason code',400);
                    }
                    break;
                case 'quantity':
                    if((!is_numeric(\Input::get('value')) && \Input::get('value') != "") || (is_numeric(\Input::get('value')) && (int)\Input::get('value') < 0))
                    {
                        return \Response::make('Please enter a valid numeric value',400);
                    }
                    break;
            }

            /*
             * New AP Product
             */
            if(is_numeric(Input::get('pk')))
            {
                //All Validation Passed, let's save
                $APProduct = new \SwiftAPProduct();
                $APProduct->{\Input::get('name')} = \Input::get('value') == "" ? null : \Input::get('value');
                if($form->product()->save($APProduct))
                {
                    switch(\Input::get('name'))
                    {
                        case 'jde_itm':
                            \Queue::push('Helper@getProductPrice',array('product_id'=>$APProduct->id,'class'=>get_class($APProduct)));
                            break;
                    }
                    \Queue::push('WorkflowActivity@updateTask',array('class'=>get_class($form),'id'=>$form->id,'user_id'=>$this->currentUser->id));
                    return \Response::make(json_encode(['encrypted_id'=>\Crypt::encrypt($APProduct->id),'id'=>$APProduct->id]));
                }
                else
                {
                    return \Response::make('Failed to save. Please retry',400);
                }

            }
            else
            {
                $APProduct = \SwiftAPProduct::find(\Crypt::decrypt(Input::get('pk')));
                if(count($APProduct))
                {
                    switch(\Input::get('name'))
                    {
                        case 'jde_itm':
                            \Queue::push('Helper@getProductPrice',array('product_id'=>$APProduct->id,'class'=>get_class($APProduct)));
                            break;
                    }

                    $APProduct->{\Input::get('name')} = \Input::get('value') == "" ? null : \Input::get('value');
                    if($APProduct->save())
                    {
                        \Queue::push('WorkflowActivity@updateTask',array('class'=>get_class($form),'id'=>$form->id,'user_id'=>$this->currentUser->id));
                        return \Response::make('Success');
                    }
                    else
                    {
                        return Response::make('Failed to save. Please retry',400);
                    }
                }
                else
                {
                    return Response::make('Error saving product: Invalid PK',400);
                }
            }
        }
        else
        {
            return Response::make('A&P Request form not found',404);
        }
    }

    /*
     * Delete Product
     */
    public function deleteProduct()
    {
        /*
         * Check Permissions
         */
        if(!$this->currentUser->hasAnyAccess([$this->permission->adminPermission,$this->permission->editPermission]))
        {
            return parent::forbidden();
        }

        $product = SwiftAPProduct::with('aprequest')->find(Crypt::decrypt(Input::get('pk')));
        if(count($product))
        {
            /*
             * Normal User but not creator = no access
             */
            if($this->currentUser->hasAccess($this->permission->editPermission) &&
                !$this->currentUser->isSuperUser() &&
                $product->aprequest->requester_user_id != $this->currentUser->id)
            {
                return Response::make('Do not delete, that which is not yours',400);
            }

            //Check what stage the form is at
            $form = $product->aprequest()->first();
            $progress = WorkflowActivity::progress($form);

            /*
             * If Form still in progress
             */
            if($progress['status']==SwiftWorkflowActivity::INPROGRESS)
            {
                foreach($progress['definition_obj'] as $d)
                {
                    if(($d->data != "" && isset($d->data->modifyproduct)) || $this->currentUser->hasAccess($this->permission->adminPermission))
                    {
                        /*
                         * At this stage we can delete products
                         */
                        $product->approval()->delete();
                        if($product->delete())
                        {
                            return Response::make('Success');
                        }
                        else
                        {
                            return Response::make('Unable to delete',400);
                        }
                    }
                }
            }

            return Response::make('Unable to delete',400);

        }
        else
        {
            return Response::make('Product not found',404);
        }
    }

    public function putErporder($apr_id)
    {
        /*
         * Check Permissions
         */
        if(!$this->currentUser->hasAnyAccess([$this->permission->adminPermission,$this->permission->ccarePermission]))
        {
            return parent::forbidden();
        }

        return $this->process('SwiftErpOrder')->saveByParent(\Crypt::decrypt($apr_id));

    }

    public function deleteErporder()
    {
        /*
         * Check Permissions
         */
        if(!$this->currentUser->hasAnyAccess([$this->permission->adminPermission,$this->permission->ccarePermission]))
        {
            return parent::forbidden();
        }

        return $this->process('SwiftErpOrder')->delete();
    }

    public function putDelivery($apr_id)
    {
        /*
         * Check Permissions
         */
        if(!$this->currentUser->hasAnyAccess([$this->permission->adminPermission,$this->permission->storePermission]))
        {
            return parent::forbidden();
        }

        $form = SwiftAPRequest::find(Crypt::decrypt($apr_id));
/*
         * Manual Validation
         */
        if(count($form))
        {
            switch(Input::get('name'))
            {
                case 'status':
                    if(!array_key_exists(Input::get('value'),SwiftDelivery::$status))
                    {
                        return Response::make('Please select a valid status',400);
                    }
                    break;
                case 'invoice_number':
                    if(!is_numeric(Input::get('value')))
                    {
                        return Response::make('Please enter a valid invoice number',400);
                    }
                    break;
            }

            /*
             * New Erp Order
             */
            if(is_numeric(Input::get('pk')))
            {
                //All Validation Passed, let's save
                $delivery = new \SwiftDelivery();
                $delivery->{Input::get('name')} = Input::get('value') == "" ? null : Input::get('value');
                if($form->delivery()->save($delivery))
                {
                    \Queue::push('WorkflowActivity@updateTask',array('class'=>get_class($form),'id'=>$form->id,'user_id'=>$this->currentUser->id));
                    return \Response::make(json_encode(['encrypted_id'=>Crypt::encrypt($delivery->id),'id'=>$delivery->id]));
                }
                else
                {
                    return \Response::make('Failed to save. Please retry',400);
                }

            }
            else
            {
                $delivery = SwiftDelivery::find(Crypt::decrypt(Input::get('pk')));
                if($delivery)
                {
                    $delivery->{Input::get('name')} = Input::get('value') == "" ? null : Input::get('value');
                    if($delivery->save())
                    {
                        \Queue::push('WorkflowActivity@updateTask',array('class'=>get_class($form),'id'=>$form->id,'user_id'=>$this->currentUser->id));
                        return \Response::make('Success');
                    }
                    else
                    {
                        return \Response::make('Failed to save. Please retry',400);
                    }
                }
                else
                {
                    return \Response::make('Error saving delivery: Invalid PK',400);
                }
            }
        }
        else
        {
            return \Response::make('A&P Request form not found',404);
        }
    }

    public function deleteDelivery()
    {
        /*
         * Check Permissions
         */
        if(!$this->currentUser->hasAnyAccess([$this->permission->adminPermission,$this->permission->storePermission]))
        {
            return parent::forbidden();
        }

        $delivery = SwiftDelivery::find(Crypt::decrypt(Input::get('pk')));

        if(count($delivery))
        {
            if($delivery->delete())
            {
                return Response::make('Success');
            }
            else
            {
                return Response::make('Unable to delete',400);
            }
        }
        else
        {
            return Response::make('Delivery not found',404);
        }
    }

    /*
     * Form approval for creator of request or admin
     */
    public function postFormapproval($apr_id)
    {
        /*
         * Check Permissions
         */
        if(!$this->currentUser->hasAnyAccess([$this->permission->adminPermission,$this->permission->editPermission]))
        {
            return parent::forbidden();
        }


        $form = SwiftAPRequest::with('product')->find(Crypt::decrypt($apr_id));

        /*
         * Manual Validation
         */
        if(count($form))
        {
            /*
             * Validation
             */

            /*
             * Normal User but not creator = no access
             */
            if($this->currentUser->hasAccess($this->permission->editPermission) &&
                !$this->currentUser->isSuperUser() &&
                $form->revisionHistory()->orderBy('created_at','ASC')->first()->user_id != $this->currentUser->id)
            {
                return Response::make('Do not publish, that which is not yours',400);
            }

            if(!count($form->product))
            {
                return Response::make('Please add some products to your form',400);
            }
            else
            {
                foreach($form->product as $p)
                {
                    $p->load('jdeproduct');
                    if($p->jde_itm == "")
                    {
                        return Response::make("Please set product name",400);
                    }
                    if((int)$p->reason_code == 0)
                    {

                        return Response::make("Please set a reason for ".trim($p->jdeproduct->DSC1),400);
                    }
                    if((int)$p->quantity <= 0)
                    {
                        return Response::make("Please set a quantity for ".trim($p->jdeproduct->DSC1),400);
                    }
                }
            }

            $approval = $form->approval()->where('type','=',SwiftApproval::APR_REQUESTER,'AND')->where('approved','=',SwiftApproval::APPROVED)->get();
            if(count($approval))
            {
                Queue::push('WorkflowActivity@updateTask',array('class'=>get_class($form),'id'=>$form->id,'user_id'=>$this->currentUser->id));
                /*
                 * Check if form has already been approved
                 */
                return Response::make('Form already approved',200);
            }
            else
            {
                $approval = new SwiftApproval([
                   'type' => SwiftApproval::APR_REQUESTER,
                   'approval_user_id' => $this->currentUser->id,
                   'approved' => SwiftApproval::APPROVED
                ]);

                if($form->approval()->save($approval))
                {
//                    Queue::push('APRequestHelper@autoexecapproval',array('aprequest_id'=>$form->id));
                    \Queue::push('WorkflowActivity@updateTask',array('class'=>get_class($form),'id'=>$form->id,'user_id'=>$this->currentUser->id));
                    return Response::make('success');
                }
                else
                {
                    return Response::make('Failed to approve form',400);
                }
            }
        }
        else
        {
            return Response::make('A&P Request form not found',404);
        }
    }

    /*
     * Approval of products for Cat Man Or Exec
     */
    public function putProductapproval($type,$product_id)
    {
        /*
         * Check Permissions
         */
        if(!$this->currentUser->hasAnyAccess(['apr-catman','apr-exec']))
        {
            return parent::forbidden();
        }

        $product = \SwiftAPProduct::find(\Crypt::decrypt($product_id));

        if(count($product))
        {
            if(\Input::get('name') == "approval_approved" && in_array(\Input::get('value'),array(\SwiftApproval::REJECTED,\SwiftApproval::APPROVED,\SwiftApproval::PENDING)))
            {
                switch((int)$type)
                {
                    case \SwiftApproval::APR_CATMAN:
                    case \SwiftApproval::APR_EXEC:
                        if(is_numeric(\Input::get('pk')))
                        {

                            /*
                             * Check if already has entry
                             */

                            $approvalCount = $product->approval()->where('type','=',$type)->count();

                            if($approvalCount)
                            {
                                return \Response::make("This form is broken. Please refresh this page.",500);
                            }

                            /*
                             * New Entry
                             */
                            //All Validation Passed, let's save
                            $approval = new \SwiftApproval(array('type'=>(int)$type,'approval_user_id'=>$this->currentUser->id, 'approved' => \Input::get('value')));
                            if($product->approval()->save($approval))
                            {
                                $apr = $product->aprequest()->first();
                                \Queue::push('WorkflowActivity@updateTask',array('class'=>get_class($apr),'id'=>$apr->id,'user_id'=>$this->currentUser->id));
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
                                    $apr = $product->aprequest()->first();
                                    \Queue::push('WorkflowActivity@updateTask',array('class'=>get_class($apr),'id'=>$apr->id,'user_id'=>$this->currentUser->id));
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
     * Approval Comment for Exec/Cat Man
     */
    public function putProductapprovalcomment($type,$product_id)
    {
        /*
         * Check Permissions
         */
        if(!$this->currentUser->hasAnyAccess(['apr-catman','apr-exec']))
        {
            return parent::forbidden();
        }

        $product = \SwiftAPProduct::find(\Crypt::decrypt($product_id));

        if(count($product))
        {
            if(\Input::get('name') == "approval_comment")
            {
                switch((int)$type)
                {
                    case \SwiftApproval::APR_CATMAN:
                    case \SwiftApproval::APR_EXEC:
                        if(is_numeric(Input::get('pk')))
                        {
                            return \Response::make('Please approve the product first',400);
                        }
                        else
                        {
                            $approval = \SwiftApproval::find(Crypt::decrypt(Input::get('pk')));
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

    public function postUpload($apr_id)
    {

        /*
         * Check Permissions
         */
        if(!$this->currentUser->hasAnyAccess([$this->permission->adminPermission,$this->permission->editPermission]))
        {
            return parent::forbidden();
        }

        $apr = SwiftAPRequest::find(Crypt::decrypt($apr_id));
        /*
         * Manual Validation
         */
        if(count($apr))
        {
            if(Input::file('file'))
            {
                $doc = new SwiftAPDocument();
                $doc->document = Input::file('file');
                if($apr->document()->save($doc))
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
            return Response::make('A&P Request form not found',404);
        }
    }

    /*
     * Delete upload
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
        if(!$this->currentUser->hasAnyAccess([$this->permission->adminPermission,$this->permission->editPermission]))
        {
            return parent::forbidden();
        }

        if(Input::get('pk') && !is_numeric(Input::get('pk')))
        {
            $doc = SwiftAPDocument::with('tag')->find(Crypt::decrypt(Input::get('pk')));
            if($doc)
            {
                //Lets check those tags
                if(count($doc->tag))
                {
                    if(Input::get('value'))
                    {
                        //It already has some tags
                        //Save those not in table
                        foreach(Input::get('value') as $val)
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
                                if(key_exists($val,SwiftTag::$aprequestTags))
                                {
                                    $tag = new SwiftTag(array('type'=>$val));
                                    if(!$doc->tag()->save($tag))
                                    {
                                        return Response::make('Error: Unable to save tags',400);
                                    }
                                }
                            }
                        }

                        //Delete values from table, not in value array

                        foreach($doc->tag as $t)
                        {
                            $found = false;
                            foreach(Input::get('value') as $val)
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
                                    return Response::make('Error: Cannot delete tag',400);
                                }
                            }
                        }
                    }
                    else
                    {
                        //Delete all existing tags
                        if(!$doc->tag()->delete())
                        {
                            return Response::make('Error: Cannot delete tag',400);
                        }
                    }
                }
                else
                {
                    //Alright, just save then
                    foreach(Input::get('value') as $val)
                    {
                        /*
                         * Validate dat tag
                         */
                        if(key_exists($val,SwiftTag::$aprequestTags))
                        {
                            $tag = new SwiftTag(array('type'=>$val));
                            if(!$doc->tag()->save($tag))
                            {
                                return Response::make('Error: Unable to save tags',400);
                            }
                        }
                        else
                        {
                            return Response::make('Error: Invalid tags',400);
                        }
                    }
                }
                return Response::make('Success');
            }
            else
            {
                return Response::make('Error: Document not found',400);
            }
        }
        else
        {
            return Response::make('Error: Document number invalid',400);
        }
    }


    /*
     * Cancel Form
     */

    public function postCancel($apr_id)
    {
        /*
         * Check Permissions
         */
        if(!$this->currentUser->hasAccess([$this->permission->adminPermission,$this->permission->editPermission]))
        {
            return parent::forbidden();
        }

        $form = SwiftAPRequest::find(Crypt::decrypt($apr_id));

        if(count($form))
        {

            /*
             * Normal User but not creator = no access
             */
            if($this->currentUser->hasAccess($this->permission->editPermission) &&
                !$this->currentUser->isSuperUser() &&
                $form->revisionHistory()->orderBy('created_at','ASC')->first()->user_id != $this->currentUser->id)
            {
                return Response::make('Do not cancel, that which is not yours',400);
            }

            if(WorkflowActivity::cancel($form))
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
     * Mark Items
     */
    public function putMark($type)
    {
        return Flag::set($type,'SwiftAPRequest',$this->permission->adminPermission);
    }

    public function getStatistics($productCategory='all')
    {
        $this->pageTitle = "Statistics";

        /*
         * Top Stats
         */
        //Top Product For the month

        $product_id = SwiftAPProduct::whereHas('approval',function($q){
                            return $q->where('approved','=',SwiftApproval::APPROVED);
                      })->select(DB::raw('TRUNCATE(SUM(price*quantity),0) as price_sum, jde_itm, TRIM(sct_jde.jdeproducts.DSC1) as label'))
                      ->join('sct_jde.jdeproducts','sct_jde.jdeproducts.itm','=','swift_ap_product.jde_itm')
                      ->groupBy('jde_itm')
                      ->whereBetween('swift_ap_product.created_at',array(new DateTime('first day of this month'),new DateTime('last day of this month')))
                      ->orderBy('price_sum','DESC')
                      ->first();

        $this->data['topstat_product'] = $product_id;

        //Top Client

        $customer_code = SwiftAPProduct::whereHas('approval',function($q){
                            return $q->where('approved','=',SwiftApproval::APPROVED);
                      })->join('swift_ap_request','swift_ap_product.aprequest_id','=','swift_ap_request.id')->select(DB::raw('TRUNCATE(SUM(price*quantity),0) as price_sum, swift_ap_request.customer_code'))
                      ->groupBy('swift_ap_request.customer_code')
                      ->whereBetween('swift_ap_product.created_at',array(new DateTime('first day of this month'),new DateTime('last day of this month')))
                      ->orderBy('price_sum','DESC')
                      ->first();

        if(count($customer_code))
        {
            $jdeCustomer = JdeCustomer::where('an8','LIKE','%'.$customer_code->customer_code.'%')->first();
            $customer_code->customer = $jdeCustomer;
        }

        $this->data['topstat_customer'] = $customer_code;

        //Top A&P Requester

        $requester = SwiftAPProduct::whereHas('approval',function($q){
                            return $q->where('approved','=',SwiftApproval::APPROVED);
                      })->join('swift_ap_request','swift_ap_product.aprequest_id','=','swift_ap_request.id')->select(DB::raw('TRUNCATE(SUM(price*quantity),0) as price_sum, swift_ap_request.requester_user_id'))
                      ->groupBy('swift_ap_request.requester_user_id')
                      ->whereBetween('swift_ap_product.created_at',array(new DateTime('first day of this month'),new DateTime('last day of this month')))
                      ->orderBy('price_sum','DESC')
                      ->first();

        if(count($requester))
        {
            $user = Sentry::find($requester->requester_user_id);
            $requester->requester = $user;
        }

        $this->data['topstat_requester'] = $requester;

        return $this->makeView("$this->rootURL/statistics");
    }

    public function postChart()
    {
        /*
         * Stats for Bar Chart
         */
        if(Input::has('type'))
        {
            try
            {
                $dateFrom = (Input::has('date-from')? Carbon::createFromFormat('Y/m/d',Input::get('date-from')) : new Carbon("first day of this month"));
                $dateTo = (Input::has('date-to') ? Carbon::createFromFormat('Y/m/d',Input::get('date-to')) : new Carbon("last day of this month"));
            }
            catch(InvalidArgumentException $e)
            {
                return Response::make($e->getMessage(),500);
            }


            if($dateFrom->diffInDays($dateTo,false) < 0)
            {
                return Response::make("'From' date must be less than 'To' Date",500);
            }


            switch(Input::get('type'))
            {
                case "product":
                    //Products
                    $products_chart = SwiftAPProduct::whereHas('approval',function($q){
                                        return $q->where('approved','=',SwiftApproval::APPROVED);
                                  })->select(DB::raw('TRUNCATE(SUM(swift_ap_product.price*swift_ap_product.quantity),0) as value, TRIM(sct_jde.jdeproducts.DSC1) as label'))
                                  ->join('sct_jde.jdeproducts','sct_jde.jdeproducts.itm','=','swift_ap_product.jde_itm')
                                  ->groupBy('jde_itm')
                                  ->whereBetween('swift_ap_product.created_at',array(new DateTime('first day of this month'),new DateTime('last day of this month')))
                                  ->orderBy('value','DESC')
                                  ->take(10)->get();

                    break;
                case "requester":
                    //Requester
                    $products_chart = SwiftAPProduct::whereHas('approval',function($q){
                                        return $q->where('approved','=',SwiftApproval::APPROVED);
                                  })->select(DB::raw('TRUNCATE(SUM(swift_ap_product.price*swift_ap_product.quantity),0) as value, CONCAT(users.first_name," ",users.last_name) as label'))
                                  ->join('swift_ap_request','swift_ap_product.aprequest_id','=','swift_ap_request.id')
                                  ->join('users','swift_ap_request.requester_user_id','=','users.id')
                                  ->groupBy('swift_ap_request.requester_user_id')
                                  ->whereBetween('swift_ap_request.created_at',array(new DateTime('first day of this month'),new DateTime('last day of this month')))
                                  ->orderBy('value','DESC')
                                  ->take(10)->get();
                    break;
                case "customer":
                    $products_chart = SwiftAPProduct::whereHas('approval',function($q){
                                        return $q->where('approved','=',SwiftApproval::APPROVED);
                                  })->select(DB::raw('TRUNCATE(SUM(swift_ap_product.price*swift_ap_product.quantity),0) as value, TRIM(sct_jde.jdecustomers.ALPH) as label'))
                                  ->join('swift_ap_request','swift_ap_product.aprequest_id','=','swift_ap_request.id')
                                  ->join('sct_jde.jdecustomers','swift_ap_request.customer_code','=','sct_jde.jdecustomers.AN8')
                                  ->groupBy('swift_ap_request.customer_code')
                                  ->whereBetween('swift_ap_request.created_at',array(new DateTime('first day of this month'),new DateTime('last day of this month')))
                                  ->orderBy('value','DESC')
                                  ->take(10)->get();
                    break;
            }
            return Response::json($products_chart);
        }
    }

    /*
     * Fetch product price based on ITM
     */
    public function getProductprice($product_code="")
    {
        if($product_code !== "")
        {
            $price = JdeSales::getProductLatestCostPrice($product_code);
                if($price)
                {
                    echo round(abs($price->ECST/$price->SOQS),4);
                    return;
                }
            echo "";
            return;
        }
        return Response::make("Product code missing",500);
    }

    /*
     * Overview : Ajax Widgets
     */
    public function getLateNodes()
    {
        $this->data['late_node_forms'] = WorkflowActivity::lateNodeByForm($this->context);
        $this->data['late_node_forms_count'] = SwiftNodeActivity::countLateNodes($this->context);

        echo View::make('workflow/overview_latenodes',$this->data)->render();
    }

    public function getPendingNodes()
    {
        $this->data['pending_node_activity'] = WorkflowActivity::statusByType($this->context);

        echo View::make('workflow/overview_pendingnodes',$this->data)->render();
    }

    public function getStories()
    {
        $this->data['stories'] = Story::fetch(Config::get('context')[$this->context]);
        $this->data['dynamicStory'] = false;

        echo View::make('story/chapter',$this->data)->render();
    }

    public function getMyrequests()
    {
        $this->data['pending_requests'] = SwiftAPRequest::getMyPending();
        $this->data['complete_requests'] = SwiftAPRequest::getMyCompleted(5);

        if(!$this->data['pending_requests']->isEmpty() || !$this->data['complete_requests']->isEmpty())
        {
            $this->data['requests_present'] = true;
            foreach(array($this->data['pending_requests'],$this->data['complete_requests']) as $apsource)
            {
                foreach($apsource as &$apr)
                {
                    $apr->current_activity = WorkflowActivity::progress($apr,$this->context);
                    $apr->activity = Helper::getMergedRevision(array('product','product.approval','order','approval','delivery','document'),$apr);
                }
            }
        }
        else
        {
            $this->data['requests_present'] = false;
        }

        echo View::make('aprequest/overview_myrequests',$this->data)->render();
    }
}