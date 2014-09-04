<?php
/*
 * Name: Order Tracking
 * Description:
 */

class OrderTrackingController extends UserController {
    
    public function __construct(){
        parent::__construct();
        $this->pageName = "Order Process";
    }
    
    /*
     * Private Functions
     */
    
    /*
     * Name: Form
     * Description: Fills in 
     */
    private function form($id,$edit=false)
    {
        $order_id = Crypt::decrypt($id);
        $order = SwiftOrder::getById($order_id);
        if(count($order))
        {
            $this->data['activity'] = Helper::getMergedRevision(array('reception','purchaseOrder','customsDeclaration','freight'),$order);
            $this->pageTitle = "{$order->name} (ID: $order->id)";
            $this->data['incoterms'] = json_encode(Helper::jsonobject_encode(SwiftFreight::$incoterms));
            $this->data['freight_type'] = json_encode(Helper::jsonobject_encode(SwiftFreight::$type));
            $this->data['business_unit'] = json_encode(Helper::jsonobject_encode(SwiftOrder::$business_unit));
            $this->data['order'] = $order;
            $this->data['tags'] = json_encode(Helper::jsonobject_encode(SwiftTag::$orderTrackingTags));
            $this->data['current_activity'] = WorkflowActivity::progress($order,'order_tracking');
            $this->data['edit'] = $edit;
            
            return $this->makeView('order-tracking/edit');
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
        if(NodeActivity::hasStartAccess('order_tracking'))
        {
            $this->pageTitle = 'Create';
            return $this->makeView('order-tracking/create');
        }
        else
        {
            return parent::forbidden();
        }
    }
    
    public function getView($id)
    {
        if(Sentry::getUser()->hasAnyAccess(['ot-edit','ot-admin']))
        {
            return Redirect::action('OrderTrackingController@getEdit',array('id'=>$id));
        }
        elseif(Sentry::getUser()->hasAnyAccess(['ot-view']))
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
        if(Sentry::getUser()->hasAnyAccess(['ot-edit','ot-admin']))
        {
            return $this->form($id,true);
        }
        elseif(Sentry::getUser()->hasAnyAccess(['ot-view']))
        {
            return Redirect::action('OrderTrackingController@getView',array('id'=>$id));
        }
        else
        {
            return parent::forbidden();
        }
    }
    
    public function getActivity($id)
    {
        $order_id = Crypt::decrypt($id);
        $order = SwiftOrder::getById($order_id);
        if(count($order))
        {
            $this->data['activity'] = Helper::getMergedRevision(array('reception','purchaseOrder','customsDeclaration','freight'),$order);
            return $this->makeView('order-tracking/edit_activity');
        }
        else
        {
            return parent::notfound();
        }        
    }
    
    public function getInbox()
    {
        $this->pageTitle = 'Inbox';
        
        //Fetch list of inbox items
    }
    
    /*
     * Lists all forms
     */
    public function getForms($type='all',$page=1)
    {
        $limitPerPage = 30;
        
        $this->pageTitle = 'Forms';
        $orderquery = SwiftOrder::take($limitPerPage)->orderBy('updated_at','desc');
        if($page > 1)
        {
            $orderquery->offset(($page-1)*$limitPerPage);
        }
        
        switch($type)
        {
            case 'inprogress':
                $orderquery->whereHas('workflow',function($q){
                    return $q->where('status','=',SwiftWorkflowActivity::INPROGRESS); 
                });
                break;
            case 'rejected':
                $orderquery->whereHas('workflow',function($q){
                   return $q->where('status','=',SwiftWorkflowActivity::REJECTED); 
                });                
                break;
            case 'completed':
                $orderquery->whereHas('workflow',function($q){
                   return $q->where('status','=',SwiftWorkflowActivity::COMPLETE); 
                });                
                break;                
        }
        
        $orders = $orderquery->get();
        
        /*
         * Fetch latest history;
         */
        foreach($orders as &$o)
        {
            $o->revision_latest = Helper::getMergedRevision(array('reception','purchaseOrder','customsDeclaration','freight'),$o);
            $o->current_activity = WorkflowActivity::progress($o);
        }
        
        $this->data['type'] = $type;
        $this->data['edit_access'] = Sentry::getUser()->hasAccess(['ot-edit','ot-admin'],false);
        $this->data['orders'] = $orders;
        $this->data['count'] = $orderquery->count();
        $this->data['page'] = $page;
        $this->data['limit_per_page'] = $limitPerPage;
        $this->data['total_pages'] = ceil($this->data['count']/$limitPerPage);
        
        return $this->makeView('order-tracking/forms');
    }
    
    /*
     * Lists all freight companies
     */
    
    public function getFreightcompany($type='all',$page=1)
    {
        $limitPerPage = 30;
        
        $this->pageTitle = 'Freight Company';  
        
        $companyquery = SwiftFreightCompany::take($limitPerPage)->orderBy('updated_at','desc');
        
        if($page > 1)
        {
            $companyquery->offset(($page-1)*$limitPerPage);
        }
                
        
        switch($type)
        {
            case "local":
                $companyquery->where('type','=',SwiftFreightCompany::LOCAL);
                break;
            case "foreign":
                $companyquery->where('type','=',SwiftFreightCompany::FOREIGN);
                break;
            case "international":
                $companyquery->where('type','=',SwiftFreightCompany::INTERNATIONAL);
                break;
        }
        
        $companies = $companyquery->get();
        
        $this->data['companies'] = $companies;
        $this->data['count'] = $companyquery->count();        
        $this->data['page'] = $page;
        $this->data['type'] = $type;
        $this->data['limit_per_page'] = $limitPerPage;
        $this->data['total_pages'] = ceil($this->data['count']/$limitPerPage);    
        
        return $this->makeView('freight-company/forms');
    }
    
    public function getCreatefreightcompanyform()
    {
        $this->pageTitle = 'Create';
        return $this->makeView('freight-company/create');        
    }
    
    public function postFreightcompanyform()
    {
        //Saving new freight companies
        $validator = Validator::make(Input::all(),
                    array('name'=>'required',
                          'type'=>array('required','in:'.implode(',',array_keys(SwiftFreightCompany::$type)))
                        )
                );
        
        if($validator->fails())
        {
            return json_encode(['success'=>0,'errors'=>$validator->errors()]);
        }
        else
        {
            $fc = new SwiftFreightCompany(Input::All());
            if($fc->save())
            {
                $fc_id = Crypt::encrypt($fc->id);
                //Success
                echo json_encode(['success'=>1,'url'=>"/order-tracking/freightcompanyform/$fc_id"]);
            }
            else
            {
                echo "";
                return false;                
            }
        }        
    }
    
    public function putFreightcompanyform()
    {
        $fc_id = Crypt::decrypt(Input::get('pk'));
        $fc = SwiftFreightCompany::find($fc_id);
        if(count($fc))
        {
            /*
             * Manual Validation
             */
            
            //Name
            if(Input::get('name') == 'name' && trim(Input::get('value')==""))
            {
                return Response::make('Please enter a name',400);
            }
            
            //Business Unit
            if(Input::get('name') == 'type' && !array_key_exists((int)Input::get('value'),SwiftFreightCompany::$type))
            {
                return Response::make('Please select a valid business unit',400);
            }
            
            //Email
            if(Input::get('name') == 'email' && Input::get('value') != "" && filter_var(Input::get('value'), FILTER_VALIDATE_EMAIL))
            {
                return Response::make('Please enter a valid email address',400);
            }
            
            /*
             * Save
             */
            $fc->{Input::get('name')} = Input::get('value');
            if($fc->save())
            {
                return Response::make('Success', 200);
            }
            else
            {
                return Response::make('Failed to save. Please retry',400);
            }
        }
        else
        {
            return Response::make('Freight Company not found',404);
        }        
    }
    
    public function deleteFreightcompanyform($fc_id)
    {
        
    }
    
    public function getFreightcompanyform($id)
    {
        $fc_id = Crypt::decrypt($id);
        $fc = SwiftFreightCompany::getById($fc_id);
        if(count($fc))
        {
            $this->data['activity'] = $fc->revisionHistory()->orderBy('created_at','desc')->get()->all();
            $this->pageTitle = "{$fc->name} (ID: $fc->id) ";
            $this->data['type'] = json_encode(Helper::jsonobject_encode(SwiftFreightCompany::$type));
            $this->data['fc'] = $fc;
            $this->data['ticker'] = $fc->freight;
            
            return $this->makeView('freight-company/edit');
        }
        else
        {
            return parent::notfound();
        }        
    }
    
    /*
     * POST Create Form
     */
    
    public function postCreate()
    {
        /*
         * Check Permission
         */
        if(!Sentry::getUser()->hasAccess('ot-admin') || !NodeActivity::hasStartAccess('order_tracking'))
        {
            return parent::forbidden();
        }
        
        $validator = Validator::make(Input::all(),
                    array('name'=>'required',
                          'business_unit'=>array('required','in:'.implode(',',array_keys(SwiftOrder::$business_unit))),
                          'email'=>'email'
                        )
                );
        
        if($validator->fails())
        {
            return json_encode(['success'=>0,'errors'=>$validator->errors()]);
        }
        else
        {
            $order = new SwiftOrder;
            $order->name = Input::get('name');
            $order->business_unit = Input::get('business_unit');
            $order->description = Input::get('description');
            if($order->save())
            {
                //Start the Workflow
                if(\WorkflowActivity::update($order,'order_tracking'))
                {
                    $order_id = Crypt::encrypt($order->id);
                    //Success
                    echo json_encode(['success'=>1,'url'=>"/order-tracking/edit/$order_id"]);
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
        if(!Sentry::getUser()->hasAccess(['ot-admin','ot-edit'],false))
        {
            return parent::forbidden();
        }
        
        $order_id = Crypt::decrypt(Input::get('pk'));
        $order = SwiftOrder::find($order_id);
        if(count($order))
        {
            /*
             * Manual Validation
             */
            
            //Name
            if(Input::get('name') == 'name' && trim(Input::get('value')==""))
            {
                return Response::make('Please enter a name',400);
            }
            
            //Business Unit
            if(Input::get('name') == 'business_unit' && !array_key_exists((int)Input::get('value'),SwiftOrder::$business_unit))
            {
                return Response::make('Please select a valid business unit',400);
            }
            
            /*
             * Save
             */
            $order->{Input::get('name')} = Input::get('value');
            if($order->save())
            {
                WorkflowActivity::update($order);
                return Response::make('Success', 200);
            }
            else
            {
                return Response::make('Failed to save. Please retry',400);
            }
        }
        else
        {
            return Response::make('Order process form not found',404);
        }
    }
    
    /*
     * Customs Declaration: REST
     */
    public function putCustomsdeclaration($order_id)
    {
        /*
         * Check Permissions
         */        
        if(!Sentry::getUser()->hasAccess(['ot-admin','ot-edit'],false))
        {
            return parent::forbidden();
        }
        
        $order = SwiftOrder::find(Crypt::decrypt($order_id));
        
        /*
         * Manual Validation
         */
        if(count($order))
        {
            switch(Input::get('name'))
            {
                case 'customs_status':
                    if(!array_key_exists(Input::get('value'),SwiftCustomsDeclaration::$status))
                    {
                        return Response::make('Please select a valid status',400);
                    }
                    break;
                case 'customs_filled_at':
//                    if(Input::get('value') == "")
//                    {
//                        return Response::make('Please enter a valid date',400);
//                    }
                    break;
                case 'customs_processed_at':
//                    if(Input::get('value') == "")
//                    {
//                        return Response::make('Please enter a valid date',400);
//                    }
                    break;
                case 'customs_reference':
                    if(!is_numeric(Input::get('value')) && Input::get('value') != "")
                    {
                        return Response::make('Please enter a numeric value',400);
                    }
                    break;
            }        

            /*
             * New Customs Declaration
             */
            if(is_numeric(Input::get('pk')))
            {
                //All Validation Passed, let's save
                $customsDeclaration = new SwiftCustomsDeclaration();
                $customsDeclaration->{Input::get('name')} = Input::get('value');
                if($order->customsDeclaration()->save($customsDeclaration))
                {
                    WorkflowActivity::update($order);
                    return Response::make(Crypt::encrypt($customsDeclaration->id));
                }
                else
                {
                    return Response::make('Failed to save. Please retry',400);
                }
                
            }
            else
            {
                $customsDeclaration = SwiftCustomsDeclaration::find(Crypt::decrypt(Input::get('pk')));
                if($customsDeclaration)
                {
                    $customsDeclaration->{Input::get('name')} = Input::get('value');
                    if($customsDeclaration->save())
                    {
                        WorkflowActivity::update($order);
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
        }
        else
        {
            return Response::make('Order process form not found',404);
        }
    }
    
    public function deleteCustomsdeclaration()
    {
        /*
         * Check Permissions
         */
        if(!Sentry::getUser()->hasAccess(['ot-admin','ot-edit'],false))
        {
            return parent::forbidden();
        }        
        
        $customs_id = Crypt::decrypt(Input::get('pk'));
        $customsDeclaration = SwiftCustomsDeclaration::find($customs_id);
        if(count($customsDeclaration))
        {
            if($customsDeclaration->delete())
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
            return Response::make('Customs entry not found',404);
        }
    }
    
    /*
     * Freight: REST
     */
    public function putFreight($order_id)
    {
        /*
         * Check Permissions
         */
        if(!Sentry::getUser()->hasAccess(['ot-admin','ot-edit'],false))
        {
            return parent::forbidden();
        }        
        
        $order = SwiftOrder::find(Crypt::decrypt($order_id));
        if(count($order))
        {
            /*
             * Manual Validation
             */
            switch(Input::get('name'))
            {
                case 'freight_type':
                    if(!array_key_exists(Input::get('value'),SwiftFreight::$type))
                    {
                        return Response::make('Please select a valid freight type',400);
                    }
                    break;
                case 'bol_no':
//                    if(trim(Input::get('value')) == "")
//                    {
//                        return Response::make('Please enter a valid bill of lading',400);
//                    }
                    break;
                case 'vessel_no':
//                    if(trim(Input::get('value')) == "")
//                    {
//                        return Response::make('Please enter a valid vessel number',400);
//                    }
                    break;
                case 'incoterms':
                    if(!array_key_exists(Input::get('value'),SwiftFreight::$incoterms))
                    {
                        return Response::make('Please select a valid incoterm',400);
                    }
                    break;
                case 'freight_etd':
                case 'freight_eta':
                    $d = DateTime::createFromFormat('d/m/Y', Input::get('value'));
                    if($d && $d->format('d/m/Y') != Input::get('value'))
                    {
                        return Response::make('Please enter a valid date',400);
                    }
                    break;
            }       

            /*
             * New Freight
             */
            if(is_numeric(Input::get('pk')))
            {
                //All Validation Passed, let's save
                $freight = new SwiftFreight();
                $freight->{Input::get('name')} = Input::get('value');
                if($order->freight()->save($freight))
                {
                    WorkflowActivity::update($order);
                    return Response::make(Crypt::encrypt($freight->id));
                }
                else
                {
                    return Response::make('Failed to save. Please retry',400);
                }
                
            }
            else
            {
                $freight = SwiftFreight::find(Crypt::decrypt(Input::get('pk')));
                if($freight)
                {
                    
                    /*
                     * Manual validation
                     */
                    
                    switch(Input::get('name'))
                    {
                        case "freight_etd":
                            if($freight->freight_eta != "" && 
                                DateTime::createFromFormat("d/m/Y", Input::get('value')) > DateTime::createFromFormat("Y-m-d", $freight->freight_eta))
                            {
                                return Response::make('ETD cannot be more than ETA',400);
                            }
                            break;
                        case "freight_eta":
                            if($freight->freight_etd != "" && 
                                DateTime::createFromFormat("d/m/Y", Input::get('value')) < DateTime::createFromFormat("Y-m-d", $freight->freight_etd))
                            {
                                return Response::make('ETA cannot be less than ETD',400);
                            }
                            break;
                    }                    
                    
                    $freight->{Input::get('name')} = Input::get('value');
                    if($freight->save())
                    {
                        WorkflowActivity::update($order);
                        return Response::make('Success');
                    }
                    else
                    {
                        return Response::make('Failed to save. Please retry',400);
                    }
                }
                else
                {
                    return Response::make('Error saving freight: Invalid PK',400);
                }
            }
        }
        else
        {
            return Response::make('Order process form not found',404);
        }        
    }
    
    public function deleteFreight()
    {
        
        /*
         * Check Permissions
         */
        if(!Sentry::getUser()->hasAccess(['ot-admin','ot-edit'],false))
        {
            return parent::forbidden();
        }        
        
        $freight_id = Crypt::decrypt(Input::get('pk'));
        $freight = SwiftFreight::find($freight_id);
        if(count($freight))
        {
            if($freight->delete())
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
            return Response::make('Freight entry not found',400);
        }
    }
    
    /*
     * Purchase Order: REST
     */
    public function putPurchaseorder($order_id)
    {
        
        /*
         * Check Permissions
         */
        if(!Sentry::getUser()->hasAccess(['ot-admin','ot-edit'],false))
        {
            return parent::forbidden();
        }        
        
        $order = SwiftOrder::find(Crypt::decrypt($order_id));
        /*
         * Manual Validation
         */
        if(count($order))
        {
            /*
             * New Purchase Order
             */
            if(is_numeric(Input::get('pk')))
            {
                //All Validation Passed, let's save
                $po = new SwiftPurchaseOrder();
                $po->{Input::get('name')} = Input::get('value');
                if($order->purchaseOrder()->save($po))
                {
                    WorkflowActivity::update($order);
                    return Response::make(Crypt::encrypt($po->id));
                }
                else
                {
                    return Response::make('Failed to save. Please retry',400);
                }
                
            }
            else
            {
                $po = SwiftPurchaseOrder::find(Crypt::decrypt(Input::get('pk')));
                if($po)
                {
                    $po->{Input::get('name')} = Input::get('value');
                    if($po->save())
                    {
                        WorkflowActivity::update($order);
                        return Response::make('Success');
                    }
                    else
                    {
                        return Response::make('Failed to save. Please retry',400);
                    }
                }
                else
                {
                    return Response::make('Error saving purchase order: Invalid PK',400);
                }
            }            
        }        
        else
        {
            return Response::make('Order process form not found',404);
        }        
    }
    
    public function deletePurchaseorder()
    {
        
        /*
         * Check Permissions
         */
        if(!Sentry::getUser()->hasAccess(['ot-admin','ot-edit'],false))
        {
            return parent::forbidden();
        }        
        
        $po_id = Crypt::decrypt(Input::get('pk'));
        $po = SwiftPurchaseOrder::find($po_id);
        if(count($po))
        {
            if($po->delete())
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
            return Response::make('Purchase order entry not found',404);
        }        
    }
    
    /*
     * Reception: REST
     */
    
    public function putReception($order_id)
    {
        
        /*
         * Check Permissions
         */
        if(!Sentry::getUser()->hasAccess(['ot-admin','ot-edit'],false))
        {
            return parent::forbidden();
        }        
        
        $order = SwiftOrder::find(Crypt::decrypt($order_id));
        /*
         * Manual Validation
         */
        if(count($order))
        {
            /*
             * New Reception
             */
            if(is_numeric(Input::get('pk')))
            {
                //All Validation Passed, let's save
                $reception = new SwiftReception();
                $reception->{Input::get('name')} = Input::get('value');
                if($order->reception()->save($reception))
                {
                    WorkflowActivity::update($order);
                    return Response::make(Crypt::encrypt($reception->id));
                }
                else
                {
                    return Response::make('Failed to save. Please retry',400);
                }
                
            }
            else
            {
                $reception = SwiftReception::find(Crypt::decrypt(Input::get('pk')));
                if($reception)
                {
                    $reception->{Input::get('name')} = Input::get('value');
                    if($reception->save())
                    {
                        WorkflowActivity::update($order);
                        return Response::make('Success');
                    }
                    else
                    {
                        return Response::make('Failed to save. Please retry',400);
                    }
                }
                else
                {
                    return Response::make('Error saving purchase order: Invalid PK',400);
                }
            }            
        }        
        else
        {
            return Response::make('Order process form not found',404);
        }        
    }
    
    public function deleteReception()
    {
        
        /*
         * Check Permissions
         */
        if(!Sentry::getUser()->hasAccess(['ot-admin','ot-edit'],false))
        {
            return parent::forbidden();
        }        
        
        $reception_id = Crypt::decrypt(Input::get('pk'));
        $reception = SwiftReception::find($reception_id);
        if(count($reception))
        {
            if($reception->delete())
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
            return Response::make('Purchase order entry not found',404);
        }        
    }
    
    /*
     * Mark Items
     */
    public function putMark($type,$id=0)
    {
        if($id != 0)
        {
            $order_id = Crypt::decrypt($id);
            $order = SwiftOrder::find(Crypt::decrypt($order_id));
            if(count($order))
            {
                switch($type)
                {
                    case "star":
                        $flag = new SwiftFlag(['type'=>SwiftFlag::STARRED]);
                        break;
                    case "important":
                        $flag = new SwiftFlag(['type'=>SwiftFlag::IMPORTANT]);
                        break;
                }
                $order->flag->save($flag);
            }
            else
            {
                return Response::make('Order process form not found',404);
            }
        }
        else
        {
            return Response::make('Unable to process: Form ID invalid',400);
        }
            
    }
    
    /*
     * Upload Document
     */
    
    public function postUpload($order_id)
    {
        
        /*
         * Check Permissions
         */
        if(!Sentry::getUser()->hasAccess(['ot-admin','ot-edit'],false))
        {
            return parent::forbidden();
        }
        
        $order = SwiftOrder::find(Crypt::decrypt($order_id));
        /*
         * Manual Validation
         */
        if(count($order))
        {
            if(Input::file('file'))
            {
                $doc = new SwiftDocument();
                $doc->document = Input::file('file');
                if($order->document()->save($doc))
                {
                    echo json_encode(['success'=>1,
                                    'url'=>$doc->getAttachedFiles()['document']->url(),
                                    'id'=>Crypt::encrypt($doc->id), 
                                    'updated_on'=>$doc->getAttachedFiles()['document']->updatedAt(), 
                                    'updated_by'=>Helper::getUserName($doc->user_id,Sentry::getUser())]);
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
            return Response::make('Order process form not found',404);
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
        if(!Sentry::getUser()->hasAccess(['ot-admin','ot-edit'],false))
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
        if(!Sentry::getUser()->hasAccess(['ot-admin','ot-edit'],false))
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
                                if(key_exists($val,SwiftTag::$orderTrackingTags))
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
                        if(key_exists($val,SwiftTag::$orderTrackingTags))
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
                WorkflowActivity::update($doc->first()->document()->first());
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
     * Cancel Workflow
     */
    
    public function postCancel($order_id)
    {
        
        /*
         * Check Permissions
         */
        if(!Sentry::getUser()->hasAccess(['ot-admin']))
        {
            return parent::forbidden();
        }        
        
        $order = SwiftOrder::find(Crypt::decrypt($order_id));
        
        if(count($order))
        {
            $workflow = $order->workflow()->first();
            $workflow->status = SwiftWorkflowActivity::REJECTED;
            if($workflow->save())
            {
                return Response::make('Workflow has been cancelled',200);
            }
            else
            {
                return Response::make('Unable to cancel workflow: Save failed',400);
            }
        }
        else
        {
            return Response::make('Order process form not found',404);
        }
    }
    
}