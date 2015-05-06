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
        $this->isSalesman = $this->data['isSalesman'] = $this->currentUser->hasAnyAccess(\Config::get("permission.{$this->context}.salesman"));

        //Can?
        $this->canCreate = $this->data['canCreate'] = $this->currentUser->hasPermission($this->createPermission);
        $this->canEdit = $this->data['canEdit'] = $this->currentUser->hasPermission($this->editPermission);
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
        $prquery = SwiftPR::query();

        if($type != 'inprogress')
        {
            //Get node definition list
            $node_definition_result = \SwiftNodeDefinition::getByWorkflowType(SwiftWorkflowType::where('name','=',$this->context)->first()->id)->all();
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

        
    }
}
    