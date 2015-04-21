<?php

class WorkflowController extends UserController {
    public function __construct(){
        parent::__construct();
        $this->pageName = "Workflow";
        $this->rootURL = "workflow";
    }

    public function getByForm($class,$encrypted_id)
    {
        $id = \Crypt::decrypt($encrypted_id);

        $workflow = SwiftWorkflowActivity::where('workflowable_id','=',$id)
                                ->where('workflowable_type','=',$class,'AND')
                                ->with(['nodes'=>function($q){
                                    return $q->orderBy('created_at','ASC');
                                },'nodes.definition','nodes.permission'])
                                ->first();
        foreach($workflow->nodes as &$n)
        {
            if(count($n->permission))
            {
                $permissions = $n->permission->toArray();
                $permissionsArray = array();
                array_walk($permissions,function($v,$k) use (&$permissionsArray){
                    $permissionsArray[] = $v['permission_name'];
                });

                $users = \Sentry::findAllUsersWithAccess($permissionsArray);
                foreach($users as $i => $u)
                {
                   if($u->isSuperUser() || !$u->activated)
                   {
                       unset($users[$i]);
                   }
                }

                if(!empty($users))
                {
                    $n->users = $users;
                }
            }
        }

        $this->data['workflow'] = $workflow;
        
        echo View::make('workflow-history',$this->data)->render();
    }

}