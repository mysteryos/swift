<?php

/* 
 * Name: Dashboard Controller
 */

Class DashboardController extends UserController {
    
    public function __construct(){
        parent::__construct();
        $this->pageName = "Dashboard";
    }    
    
    public function getIndex()
    {
        /*
         * Todo List
         */
        
        $this->data['todoList'] = false;
        
        //TodoList - Order-tracking Edit User
        if(in_array(\Config::get('permission.order-tracking.edit'),(array)array_keys($this->currentUser->getMergedPermissions())))
        {
            $this->data['todoList']['order-tracking'] = SwiftOrder::orderBy('swift_order.updated_at','desc')
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
                                            },'=',0)->remember(5)->get();
        }
        
        //TodoList - A&P Request Edit User
        if(in_array(\Config::get('permission.aprequest.edit'),(array)array_keys($this->currentUser->getMergedPermissions())))
        {
            $this->data['todoList']['aprequest'] = SwiftAPRequest::orderBy('updated_at','desc')
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
        }
        
        /*
         * Static Stories
         */
        
        
        
        /*
         * Actionable Stories
         */
        
        
        
        
        return $this->makeView('dashboard');
    }
}