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
        $apr = SwiftOrder::getById($order_id);
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
            $this->comment($apr);
            
            /*
             * Data
             */
            $this->data['activity'] = Helper::getMergedRevision(array('reception','purchaseOrder','customsDeclaration','freight','shipment','document'),$order);
            $this->pageTitle = "{$order->name} (ID: $order->id)";
            $this->data['incoterms'] = json_encode(Helper::jsonobject_encode(SwiftFreight::$incoterms));
            $this->data['freight_type'] = json_encode(Helper::jsonobject_encode(SwiftFreight::$type));
            $this->data['business_unit'] = json_encode(Helper::jsonobject_encode(SwiftOrder::$business_unit));
            $this->data['customs_status'] = json_encode(Helper::jsonobject_encode(SwiftCustomsDeclaration::$status));
            $this->data['shipment_type'] = json_encode(Helper::jsonobject_encode(SwiftShipment::$type));
            $this->data['order'] = $order;
            $this->data['tags'] = json_encode(Helper::jsonobject_encode(SwiftTag::$orderTrackingTags));
            $this->data['current_activity'] = WorkflowActivity::progress($order,'order_tracking');
            $this->data['edit'] = $edit;
            $this->data['flag_important'] = Flag::isImportant($order);
            $this->data['flag_starred'] = Flag::isStarred($order);
            
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
        if(NodeActivity::hasStartAccess('aprequest'))
        {
            $this->pageTitle = 'Create';
            return $this->makeView('aprequest/create');
        }
        else
        {
            return parent::forbidden();
        }
    }
    
    public function getView($id)
    {
        if(Sentry::getUser()->hasAnyAccess([$this->editPermission,$this->adminPermission]))
        {
            return Redirect::action('APRequestController@getEdit',array('id'=>$id));
        }
        elseif(Sentry::getUser()->hasAnyAccess([$this->viewPermission]))
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
        if(Sentry::getUser()->hasAnyAccess([$this->editPermission,$this->adminPermission]))
        {
            return $this->form($id,true);
        }
        elseif(Sentry::getUser()->hasAnyAccess([$this->viewPermission]))
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
        $aprequestquery = SwiftApRequest::take($limitPerPage)->orderBy('updated_at','desc');
        if($page > 1)
        {
            $aprequestquery->offset(($page-1)*$limitPerPage);
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
                   return $q->where('type','=',SwiftFlag::STARRED,'AND')->where('user_id','=',Sentry::getUser()->id,'AND')->where('active','=',SwiftFlag::ACTIVE); 
                });                
                break;
            case 'important':
                $aprequestquery->whereHas('flag',function($q){
                   return $q->where('type','=',SwiftFlag::IMPORTANT,'AND'); 
                });                
                break;
        }
        
        $forms = $aprequestquery->get();
        
        /*
         * Fetch latest history;
         */
        foreach($forms as &$f)
        {
            //Set Revision
            $f->revision_latest = Helper::getMergedRevision(array('product'),$f);
            //Set Current Workflow Activity
            $f->current_activity = WorkflowActivity::progress($f);
            //Set Starred/important
            $f->flag_starred = Flag::isStarred($f);
            $f->flag_important = Flag::isImportant($f);
            $f->flag_read = Flag::isRead($f);
        }
        
        //Get node definition list
//        $node_definition_result = SwiftNodeDefinition::getByWorkflowType(SwiftWorkflowType::where('name','=','order_tracking')->first()->id)->all();
//        $node_definition_list = array();
//        foreach($node_definition_result as $v)
//        {
//            $node_definition_list[$v->id] = $v->label;
//        }
//        
        
        //The Data
        $this->data['type'] = $type;
        $this->data['isAdmin'] = Sentry::getUser()->hasAnyAccess([$this->adminPermission]);
        $this->data['edit_access'] = Sentry::getUser()->hasAnyAccess([$this->editPermission,$this->adminPermission]);
        $this->data['forms'] = $forms;
        $this->data['count'] = $aprequestquery->count();
        $this->data['page'] = $page;
        $this->data['limit_per_page'] = $limitPerPage;
        $this->data['total_pages'] = ceil($this->data['count']/$limitPerPage);
        $this->data['filter'] = Input::has('filter') ? "?filter=1" : "";
        $this->data['rootURL'] = $this->rootURL;
//        $this->data['node_definition_list'] = $node_definition_list;
        
        return $this->makeView('aprequest/forms');
    }
    
    /*
     * POST Create Form
     */
    
    public function postCreate()
    {
        /*
         * Check Permission
         */
        if(!Sentry::getUser()->hasAccess($this->adminPermission) || !NodeActivity::hasStartAccess('aprequest'))
        {
            return parent::forbidden();
        }
        
        $validator = Validator::make(Input::all(),
                        array('name'=>'required')
                    );
        
        if($validator->fails())
        {
            return json_encode(['success'=>0,'errors'=>$validator->errors()]);
        }
        else
        {
            $aprequest = new SwiftAPRequest(Input::All());
            if($aprequest->save())
            {
                //Start the Workflow
                if(\WorkflowActivity::update($aprequest,'aprequest'))
                {
                    //Success
                    echo json_encode(['success'=>1,'url'=>Helper::generateUrl($$aprequest)]);
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
        if(!Sentry::getUser()->hasAnyAccess([$this->adminPermission,$this->editPermission]))
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
    
    /*
     * Mark Items
     */
    public function putMark($type)
    {
        return Flag::set($type,'SwiftAPRequest',$this->adminPermission);
    }    
}