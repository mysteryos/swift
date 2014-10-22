<?php
/*
 * Name:
 * Description:
 */

class APRequestController extends UserController {
    
    public function __construct(){
        parent::__construct();
        $this->pageName = "A&P Request";
        $this->rootURL = "aprequest";
        $this->adminPermission = "apr-admin";
        $this->viewPermission = "apr-view";
        $this->editPermission = "apr-edit";
        $this->ccarePermission = "apr-ccare";
        $this->storePermission = "apr-store";
    }
    
    /*
     * Overview
     */
    
    public function getOverview()
    {
        $this->pageTitle = 'Overview';
    }
    
    /*
     * Name: Form
     * Description: Fills in 
     */
    private function form($id,$edit=false)
    {
        $apr_id = Crypt::decrypt($id);
        $apr = SwiftAPRequest::getById($apr_id);
        if(count($apr))
        {
            /*
             * Set Read
             */
            
            if(!Flag::isRead($apr))
            {
                Flag::toggleRead($apr);
            }
            
            /*
             * Enable Commenting
             */
            $this->enableComment($apr);
            
            /*
             * Data
             */
            $this->data['activity'] = Helper::getMergedRevision(array('product','document','delivery','order','product.approvalexec','product.approvalcatman'),$apr);
            $this->pageTitle = "{$apr->name} (ID: $apr->id)";
            $this->data['form'] = $apr;
//            $this->data['tags'] = json_encode(Helper::jsonobject_encode(SwiftTag::$orderTrackingTags));
            $this->data['product_reason_code'] = json_encode(Helper::jsonobject_encode(SwiftAPProduct::$reason));
            $this->data['approval_code'] = json_encode(Helper::jsonobject_encode(SwiftApproval::$approved));
            $this->data['erporder_type'] = json_encode(Helper::jsonobject_encode(SwiftErpOrder::$type));
            $this->data['erporder_status'] = json_encode(Helper::jsonobject_encode(SwiftErpOrder::$status));
            $this->data['delivery_status'] = json_encode(Helper::jsonobject_encode(SwiftDelivery::$status));
            $this->data['current_activity'] = WorkflowActivity::progress($apr,'aprequest');
            $this->data['edit'] = $edit;
            $this->data['flag_important'] = Flag::isImportant($apr);
            $this->data['flag_starred'] = Flag::isStarred($apr);
            $this->data['tags'] = json_encode(Helper::jsonobject_encode(SwiftTag::$aprequestTags));
            $this->data['rootURL'] = $this->rootURL;
            $this->data['isAdmin'] = $this->currentUser->hasAnyAccess([$this->adminPermission]);
            $this->data['isCcare'] = $this->currentUser->hasAccess($this->ccarePermission);
            $this->data['isStore'] = $this->currentUser->hasAccess($this->storePermission);
            $this->data['canPublish'] = $this->data['canModifyProduct'] = $this->data['canAddProduct'] = false;
            $this->data['isCatMan'] = $this->currentUser->hasAccess(['apr-catman']);
            $this->data['isExec'] = $this->currentUser->hasAccess(['apr-exec']);
            $this->data['isCreator'] = $this->currentUser->id = $apr->revisionHistory()->first()->user_id ? true : false;
            
            /*
             * See if can publish
             */
            
            if($edit == true)
            {
                if($this->data['current_activity']['status'] == SwiftWorkflowActivity::INPROGRESS)
                {
                    if(!isset($this->data['current_activity']['definition_obj']))
                    {
                        /*
                         * Detect buggy workflows
                         */
                        WorkflowActivity::update($apr);
                    }
                    
                    foreach($this->data['current_activity']['definition_obj'] as $d)
                    {
                        if($d->data != "")
                        {
                            if(isset($d->data->addproduct))
                            {
                                $this->data['canAddProduct'] = true;
                            }
                            
                            if(isset($d->data->modifyproduct))
                            {
                                $this->data['canModifyProduct'] = true;
                            }
                            
                            if(isset($d->data->manualpublish) && ($this->data['isAdmin'] || $apr->revisionHistory()->orderBy('created_at','ASC')->first()->user_id == $this->currentUser->id))
                            {
                                $this->data['canPublish'] = true;
                                break;
                            }
                        }
                    }
                }
            }
            
            if(count($apr->product))
            {
                foreach($apr->product as &$p)
                {
                    $p->approvalstatus = SwiftApproval::PENDING;
                    if(count($p->approvalcatman) && $p->approvalcatman->approved != SwiftApproval::PENDING)
                    {
                        $p->approvalstatus = $p->approvalcatman->approved;
                    }

                    if(count($p->approvalexec) && $p->approvalexec->approved != SwiftApproval::PENDING)
                    {
                        $p->approvalstatus = $p->approvalexec->approved;
                    }
                    
                }
            }

            
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
        //Check Permission
        if(NodeActivity::hasStartAccess('aprequest'))
        {
            $this->pageTitle = 'Create';
            return $this->makeView("$this->rootURL/create");
        }
        else
        {
            return parent::forbidden();
        }
    }
    
    public function getView($id)
    {
        if($this->currentUser->hasAnyAccess([$this->editPermission,$this->adminPermission]))
        {
            return Redirect::action('APRequestController@getEdit',array('id'=>$id));
        }
        elseif($this->currentUser->hasAnyAccess([$this->viewPermission]))
        {
            return $this->form($id,false);
        }
        else
        {
            return parent::forbidden();
        }
    }
    
    public function getEdit($id)
    {
        if($this->currentUser->hasAnyAccess([$this->editPermission,$this->adminPermission]))
        {
            return $this->form($id,true);
        }
        elseif($this->currentUser->hasAnyAccess([$this->viewPermission]))
        {
            return Redirect::action('APRequestController@getView',array('id'=>$id));
        }
        else
        {
            return parent::forbidden();
        }
    }
    
    /*
     * Lists all forms
     */
    public function getForms($type='all',$page=1)
    {
        $limitPerPage = 30;
        
        $this->pageTitle = 'Forms';
        
        //Check Edit Access
        $this->data['edit_access'] = $this->currentUser->hasAnyAccess([$this->editPermission,$this->adminPermission]);           
        
        //Check user group
        if(!$this->data['edit_access'] && $type='inprogress')
        {
            $type='all';
        }        
        
        $aprequestquery = SwiftApRequest::orderBy('updated_at','desc');
        
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
            
            //Get node definition list
            $node_definition_result = SwiftNodeDefinition::getByWorkflowType(SwiftWorkflowType::where('name','=','aprequest')->first()->id)->all();
            $node_definition_list = array();
            foreach($node_definition_result as $v)
            {
                $node_definition_list[$v->id] = $v->label;
            }
            $this->data['node_definition_list'] = $node_definition_list;            
        }        
        
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
        }
        
        //Filters
        if(Input::has('filter'))
        {
            
            if(Session::has('apr_form_filter'))
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
                foreach($f->current_activity['definition'] as $d)
                {
                    if(NodeActivity::hasAccess($d,SwiftNodePermission::RESPONSIBLE))
                    {
                        $hasAccess = true;
                        break;
                    }
                }
                
                /*
                 * No Access : We Remove order from list
                 */
                if(!$hasAccess)
                {
                    unset($forms[$k]);
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
        $this->data['isAdmin'] = $this->currentUser->hasAnyAccess([$this->adminPermission]);
        $this->data['forms'] = $forms;
        $this->data['count'] = $aprequestquery->count();
        $this->data['page'] = $page;
        $this->data['limit_per_page'] = $limitPerPage;
        $this->data['total_pages'] = ceil($this->data['count']/$limitPerPage);
        $this->data['filter'] = Input::has('filter') ? "?filter=1" : "";
        $this->data['rootURL'] = $this->rootURL;
        
        return $this->makeView("$this->rootURL/forms");
    }
    
    /*
     * POST Create Form
     */
    
    public function postCreate()
    {
        /*
         * Check Permission
         */
        if(!$this->currentUser->hasAccess($this->editPermission) || !NodeActivity::hasStartAccess('aprequest'))
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
                if(\WorkflowActivity::update($aprequest,'aprequest'))
                {
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
        if(!$this->currentUser->hasAnyAccess([$this->adminPermission,$this->editPermission]))
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
            if(Input::get('name') == 'name' && trim(Input::get('value')==""))
            {
                return Response::make('Please enter a name',400);
            }
            
             if(Input::get('name') == 'customer_code' && trim(Input::get('value')==""))
            {
                return Response::make('Please enter a customer name',400);
            }           
            
            /*
             * Save
             */
            $aprequest->{Input::get('name')} = Input::get('value') == "" ? null : Input::get('value');
            if($aprequest->save())
            {
                WorkflowActivity::update($aprequest);
                return Response::make('Success', 200);
            }
            else
            {
                return Response::make('Failed to save. Please retry',400);
            }
        }
        else
        {
            return Response::make('A&P Request form not found',404);
        }
    }
    
    public function putProduct($apr_id)
    {
        /*  
         * Check Permissions
         */        
        if(!$this->currentUser->hasAnyAccess([$this->adminPermission,$this->editPermission]))
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
                    if(!array_key_exists(Input::get('value'),SwiftAPProduct::$reason))
                    {
                        return Response::make('Please select a valid reason code',400);
                    }
                    break;
                case 'quantity':
                    if((!is_numeric(Input::get('value')) && Input::get('value') != "") || (is_numeric(Input::get('value')) && (int)Input::get('value') < 0))
                    {
                        return Response::make('Please enter a valid numeric value',400);
                    }
                    break;
            }       

            /*
             * New AP Product
             */
            if(is_numeric(Input::get('pk')))
            {
                //All Validation Passed, let's save
                $APProduct = new SwiftAPProduct();
                $APProduct->{Input::get('name')} = Input::get('value') == "" ? null : Input::get('value');
                if($form->product()->save($APProduct))
                {
                    WorkflowActivity::update($form);
                    return Response::make(json_encode(['encrypted_id'=>Crypt::encrypt($APProduct->id),'id'=>$APProduct->id]));
                }
                else
                {
                    return Response::make('Failed to save. Please retry',400);
                }
                
            }
            else
            {
                $APProduct = SwiftAPProduct::find(Crypt::decrypt(Input::get('pk')));
                if($APProduct)
                {
                    $APProduct->{Input::get('name')} = Input::get('value') == "" ? null : Input::get('value');
                    if($APProduct->save())
                    {
                        WorkflowActivity::update($form);
                        return Response::make('Success');
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
        if(!$this->currentUser->hasAnyAccess([$this->adminPermission,$this->editPermission]))
        {
            return parent::forbidden();
        }
        
        $product = SwiftAPProduct::find(Crypt::decrypt(Input::get('pk')));
        if(count($product))
        {
            /*
             * Normal User but not creator = no access
             */
            if($this->currentUser->hasAccess($this->editPermission) && 
                !$this->currentUser->isSuperUser() && 
                $form->revisionHistory()->orderBy('created_at','ASC')->first()->user_id != $this->currentUser->id)
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
                    if(($d->data != "" && isset($d->data->modifyproduct)) || $this->currentUser->hasAccess($this->adminPermission))
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
        if(!$this->currentUser->hasAnyAccess([$this->adminPermission,$this->ccarePermission]))
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
                    if(!array_key_exists(Input::get('value'),SwiftErpOrder::$status))
                    {
                        return Response::make('Please select a valid status',400);
                    }
                    break;
                case 'type':
                    if(!array_key_exists(Input::get('value'),SwiftErpOrder::$type))
                    {
                        return Response::make('Please select a valid type',400);
                    }
                    break;
            }
            
            /*
             * New Erp Order
             */
            if(is_numeric(Input::get('pk')))
            {
                //All Validation Passed, let's save
                $erpOrder = new SwiftErpOrder();
                $erpOrder->{Input::get('name')} = Input::get('value') == "" ? null : Input::get('value');
                if($form->order()->save($erpOrder))
                {
                    WorkflowActivity::update($form);
                    return Response::make(json_encode(['encrypted_id'=>Crypt::encrypt($erpOrder->id),'id'=>$erpOrder->id]));
                }
                else
                {
                    return Response::make('Failed to save. Please retry',400);
                }
                
            }
            else
            {
                $erpOrder = SwiftErpOrder::find(Crypt::decrypt(Input::get('pk')));
                if($erpOrder)
                {
                    $erpOrder->{Input::get('name')} = Input::get('value') == "" ? null : Input::get('value');
                    if($erpOrder->save())
                    {
                        WorkflowActivity::update($form);
                        return Response::make('Success');
                    }
                    else
                    {
                        return Response::make('Failed to save. Please retry',400);
                    }
                }
                else
                {
                    return Response::make('Error saving order: Invalid PK',400);
                }
            }
        }
        else
        {
            return Response::make('A&P Request form not found',404);
        }
        
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
        
        $erpOrder = SwiftErpOrder::find(Crypt::decrypt(Input::get('pk')));
        
        if(count($erpOrder))
        {
            if($erpOrder->delete())
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
            return Response::make('JDE order not found',404);
        }        
    }
    
    public function putDelivery($apr_id)
    {
        /*  
         * Check Permissions
         */        
        if(!$this->currentUser->hasAnyAccess([$this->adminPermission,$this->storePermission]))
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
                $delivery = new SwiftDelivery();
                $delivery->{Input::get('name')} = Input::get('value') == "" ? null : Input::get('value');
                if($form->delivery()->save($delivery))
                {
                    WorkflowActivity::update($form);
                    return Response::make(json_encode(['encrypted_id'=>Crypt::encrypt($delivery->id),'id'=>$delivery->id]));
                }
                else
                {
                    return Response::make('Failed to save. Please retry',400);
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
                        WorkflowActivity::update($form);
                        return Response::make('Success');
                    }
                    else
                    {
                        return Response::make('Failed to save. Please retry',400);
                    }
                }
                else
                {
                    return Response::make('Error saving delivery: Invalid PK',400);
                }
            }
        }
        else
        {
            return Response::make('A&P Request form not found',404);
        }        
    }
    
    public function deleteDelivery()
    {
        /*
         * Check Permissions
         */
        if(!$this->currentUser->hasAnyAccess([$this->adminPermission,$this->storePermission]))
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
        if(!$this->currentUser->hasAnyAccess([$this->adminPermission,$this->editPermission]))
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
            if($this->currentUser->hasAccess($this->editPermission) && 
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
                WorkflowActivity::update($form);
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
                    WorkflowActivity::update($form);
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
        
        $product = SwiftAPProduct::find(Crypt::decrypt($product_id));
        
        if(count($product))
        {
            if(Input::get('name') == "approval_approved" && in_array(Input::get('value'),array(SwiftApproval::REJECTED,SwiftApproval::APPROVED,SwiftApproval::PENDING)))
            {
                switch((int)$type)
                {
                    case SwiftApproval::APR_CATMAN:
                    case SwiftApproval::APR_EXEC:
                        if(is_numeric(Input::get('pk')))
                        {
                            /*
                             * New Entry
                             */
                            //All Validation Passed, let's save
                            $approval = new SwiftApproval(array('type'=>(int)$type,'approval_user_id'=>$this->currentUser->id, 'approved' => Input::get('value')));
                            if($product->approval()->save($approval))
                            {
                                $apr = $product->aprequest()->first();
                                WorkflowActivity::update($apr);
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
                                    $apr = $product->aprequest()->first();
                                    WorkflowActivity::update($apr);
                                    return Response::make('Success');
                                }
                                else
                                {
                                    return Response::make('Failed to save. Please retry',400);
                                }
                            }
                            else
                            {
                                return Response::make('Error saving customs information: Invalid PK',400);
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
        
        $product = SwiftAPProduct::find(Crypt::decrypt($product_id));
        
        if(count($product))
        {
            if(Input::get('name') == "approval_comment")
            {
                switch((int)$type)
                {
                    case SwiftApproval::APR_CATMAN:
                    case SwiftApproval::APR_EXEC:
                        if(is_numeric(Input::get('pk')))
                        {
                            return Response::make('Please approve the product first',400);
                        }
                        else
                        {
                            $approval = SwiftApproval::find(Crypt::decrypt(Input::get('pk')));
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
    
    public function postUpload($apr_id)
    {
        
        /*
         * Check Permissions
         */
        if(!$this->currentUser->hasAnyAccess([$this->adminPermission,$this->editPermission]))
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
                $doc = new SwiftDocument();
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
        if(!$this->currentUser->hasAnyAccess([$this->adminPermission,$this->editPermission]))
        {
            return parent::forbidden();
        }        
        
        $doc = SwiftDocument::find(Crypt::decrypt($doc_id));
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
        
        if(Input::get('pk') && !is_numeric(Input::get('pk')))
        {
            $doc = SwiftDocument::with('tag')->find(Crypt::decrypt(Input::get('pk')));
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
//                WorkflowActivity::update($doc->aprequest);
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
        if(!$this->currentUser->hasAccess([$this->adminPermission,$this->editPermission]))
        {
            return parent::forbidden();
        }        
        
        $form = SwiftAPRequest::find(Crypt::decrypt($apr_id));
        
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
            
            $workflow = $form->workflow()->first();
            if($workflow->status == SwiftWorkflowActivity::INPROGRESS)
            {
                $workflow->status = SwiftWorkflowActivity::REJECTED;
                if($workflow->save())
                {
                    return Response::make('Workflow has been cancelled',200);
                }
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
        return Flag::set($type,'SwiftAPRequest',$this->adminPermission);
    }    
}