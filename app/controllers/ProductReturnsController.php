<?php

class ProductReturnsController extends UserController {
    
    public function __construct()
    {
        parent::__construct();
        $this->pageName = "Product Returns";
        $this->rootURL = $this->context = "product-returns";
        $this->adminPermission = \Config::get("permission.{$this->context}.admin");
        $this->viewPermission = \Config::get("permission.{$this->context}.view");
        $this->editPermission = \Config::get("permission.{$this->context}.edit");
        $this->createPermission = \Config::get("permission.{$this->context}.create");
        $this->ccarePermission = \Config::get("permission.{$this->context}.ccare");
        $this->dispatchPermission = \Config::get("permission.{$this->context}.dispatch");
        $this->receptionPermission = \Config::get("permission.{$this->context}.reception");
        $this->storeValidationPermission = \Config::get("permission.{$this->context}.storevalidation");
        $this->retailManagerPermission = \Config::get("permission.{$this->context}.retailmanager");
    }
    
    
    public function getIndex()
    {
        return Redirect::to('/'.$this->context.'/overview');
    }    
    
    public function getOverview()
    {
        
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
            
            //Form Data
            $this->pageTitle = "{$pr->customer_name} (ID: $pr->id)";
            $this->data['form'] = $pr;            
            $this->data['product_reason_code'] = json_encode(Helper::jsonobject_encode(SwiftPRReason::getAll()));
            $this->data['erporder_type'] = json_encode(Helper::jsonobject_encode(SwiftErpOrder::$typeReturn));
            $this->data['erporder_status'] = json_encode(Helper::jsonobject_encode(SwiftErpOrder::$status));
            $this->data['flag_starred'] = Flag::isStarred($pr);
            $this->data['tags'] = json_encode(Helper::jsonobject_encode(SwiftTag::$prTags));
            
            
            //Permissions
            $this->data['isRetailManager'] = $this->currentUser->hasAccess($this->retailManagerPermission);
            $this->data['isCcare'] = $this->currentUser->hasAccess($this->ccarePermission);
            $this->data['isReception'] = $this->currentUser->hasAccess($this->storePermission);
            $this->data['isDispatch'] = $this->currentUser->hasAccess($this->dispatchPermission);
            $this->data['isStoreValidation'] = $this->currentUser->hasAccess($this->storeValidationPermission);
            $this->data['isOwner'] = ($pr->owner_user_id === $this->currentUser->id);
            
            return $this->makeView('product-returns/edit');
        }
        else
        {
            return parent::notfound();
        }
    }
    
    public function getView($id,$override=false)
    {
        if($override === true)
        {
            return $this->form($id,false);
        }
        
        if($this->currentUser->hasAnyAccess([$this->editPermission,$this->adminPermission]))
        {
            return Redirect::action('ProductReturnsController@getEdit',array('id'=>$id));
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
            return Redirect::action('ProductReturnsController@getView',array('id'=>$id));
        }
        else
        {
            return parent::forbidden();
        }
    }
}
    