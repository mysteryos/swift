<?php
class AcPayableController extends UserController {
    
    public function __construct(){
        parent::__construct();
        $this->pageName = "Accounts Payable";
        $this->rootURL = "acpayable";
        $this->adminPermission = "ap-admin";
        $this->viewPermission = "ap-view";
        $this->editPermission = "ap-edit";
    }
    
    /*
     * Overview
     */
    
    public function getOverview()
    {
        $this->pageTitle = 'Overview';
        $this->data['inprogress_limit'] = 15;
        
        
        $aprequest_inprogress = $aprequest_inprogress_important = $aprequest_inprogress_responsible = $aprequest_inprogress_important_responsible = array();
        
        $aprequest_inprogress = SwiftAPRequest::orderBy('updated_at','desc')
                            ->with('workflow','workflow.nodes')->whereHas('workflow',function($q){
                                return $q->where('status','=',SwiftWorkflowActivity::INPROGRESS,'AND')
                                        ->whereHas('nodes',function($q){
                                             return $q->where('user_id','=',0)->whereHas('permission',function($q){
                                                 return $q->where('permission_type','=',SwiftNodePermission::RESPONSIBLE,'AND')
                                                        ->whereIn('permission_name',(array)array_keys($this->currentUser->getMergedPermissions()));
                                            },'=',0);
                                        }); 
                            })->whereHas('flag',function($q){
                                return $q->where('type','=',SwiftFlag::IMPORTANT,'AND')->where('active','=',SwiftFlag::ACTIVE);
                            },'=',0)->take($this->data['inprogress_limit'])->get();
                            
        $aprequest_inprogress_count = SwiftAPRequest::orderBy('updated_at','desc')
                            ->with('workflow','workflow.nodes')->whereHas('workflow',function($q){
                                return $q->where('status','=',SwiftWorkflowActivity::INPROGRESS,'AND')
                                        ->whereHas('nodes',function($q){
                                             return $q->where('user_id','=',0)->whereHas('permission',function($q){
                                                 return $q->where('permission_type','=',SwiftNodePermission::RESPONSIBLE,'AND')
                                                        ->whereIn('permission_name',(array)array_keys($this->currentUser->getMergedPermissions()));
                                            },'=',0);
                                        }); 
                            })->whereHas('flag',function($q){
                                return $q->where('type','=',SwiftFlag::IMPORTANT,'AND')->where('active','=',SwiftFlag::ACTIVE);
                            },'=',0)->count();
                            
        $aprequest_inprogress_important = SwiftAPRequest::orderBy('updated_at','desc')
                           ->with('workflow','workflow.nodes')->whereHas('workflow',function($q){
                               return $q->where('status','=',SwiftWorkflowActivity::INPROGRESS,'AND')
                                       ->whereHas('nodes',function($q){
                                            return $q->where('user_id','=',0)->whereHas('permission',function($q){
                                                return $q->where('permission_type','=',SwiftNodePermission::RESPONSIBLE,'AND')
                                                       ->whereIn('permission_name',(array)array_keys($this->currentUser->getMergedPermissions()));
                                           },'=',0);
                                       }); 
                           })->whereHas('flag',function($q){
                               return $q->where('type','=',SwiftFlag::IMPORTANT,'AND')->where('active','=',SwiftFlag::ACTIVE);
                           })->get();       
                            
       $aprequest_inprogress_responsible = SwiftAPRequest::orderBy('updated_at','desc')
                            ->with('workflow','workflow.nodes')->whereHas('workflow',function($q){
                                return $q->where('status','=',SwiftWorkflowActivity::INPROGRESS,'AND')
                                        ->whereHas('nodes',function($q){
                                             return $q->where('user_id','=',0)->whereHas('permission',function($q){
                                                 return $q->where('permission_type','=',SwiftNodePermission::RESPONSIBLE,'AND')
                                                        ->whereIn('permission_name',(array)array_keys($this->currentUser->getMergedPermissions()));
                                            });
                                        }); 
                            })->whereHas('flag',function($q){
                                return $q->where('type','=',SwiftFlag::IMPORTANT,'AND')->where('active','=',SwiftFlag::ACTIVE);
                            },'=',0)->get(); 
                            
       $aprequest_inprogress_important_responsible = SwiftAPRequest::orderBy('updated_at','desc')
                            ->with('workflow','workflow.nodes')->whereHas('workflow',function($q){
                                return $q->where('status','=',SwiftWorkflowActivity::INPROGRESS,'AND')
                                        ->whereHas('nodes',function($q){
                                             return $q->where('user_id','=',0)->whereHas('permission',function($q){
                                                 return $q->where('permission_type','=',SwiftNodePermission::RESPONSIBLE,'AND')
                                                        ->whereIn('permission_name',(array)array_keys($this->currentUser->getMergedPermissions()));
                                            });
                                        }); 
                            })->whereHas('flag',function($q){
                                return $q->where('type','=',SwiftFlag::IMPORTANT,'AND')->where('active','=',SwiftFlag::ACTIVE);
                            })->get();                            
        
        $aprequest_inprogress = $aprequest_inprogress->diff($aprequest_inprogress_responsible);
        $aprequest_inprogress_important = $aprequest_inprogress_important->diff($aprequest_inprogress_important_responsible);
        
        if(count($aprequest_inprogress) == 0 || count($aprequest_inprogress_important) == 0 || count($aprequest_inprogress_responsible) == 0 || count($aprequest_inprogress_important_responsible) == 0)
        {
            $this->data['in_progress_present'] = true;
        }
        else
        {
            $this->data['in_progress_present'] = false;
        }
        
        foreach(array($aprequest_inprogress,$aprequest_inprogress_responsible,$aprequest_inprogress_important,$aprequest_inprogress_important_responsible) as $aprequestarray)
        {
            foreach($aprequestarray as &$apr)
            {
                $apr->current_activity = WorkflowActivity::progress($apr,'aprequest');
                $apr->activity = Helper::getMergedRevision(array('product','product.approval','order','approval','delivery','document'),$apr);
            }
        }     

        $this->data['inprogress'] = $aprequest_inprogress;
        $this->data['inprogress_responsible'] = $aprequest_inprogress_responsible;
        $this->data['inprogress_important'] = $aprequest_inprogress_important;
        $this->data['inprogress_important_responsible'] = $aprequest_inprogress_important_responsible;
        /*$this->data['aprequest_storage'] = $storage_array*/
        $this->data['isAdmin'] = $this->currentUser->hasAccess(array($this->adminPermission));
        
        return $this->makeView('aprequest/overview');        
        
    }    
}