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

                if(!in_array($n->definition->label,["Start","Preparation"]))
                {
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
                        $userArray = [];
                        foreach($users as $u)
                        {
                            $userArray[] = $u->first_name." ".$u->last_name;
                        }

                        $n->users = implode(",",$userArray);
                    }
                }
            }
        }

        $this->data['workflow'] = $workflow;
        
        echo \View::make('workflow-history',$this->data)->render();
    }

    public function getForceUpdate($context,$encrypted_id)
    {
        $id = \Crypt::decrypt($encrypted_id);
        $class = \Config::get("context.$context");
        if($class)
        {
            $form = $class::find($id);
            $workflow = $form->workflow()->get();
            \WorkflowActivity::update($form,$context);
            $workflowUpdated = $form->workflow()->get();
            if($workflowUpdated->updated_at->diffInSeconds($workflow->updated_at))
            {
                return \Response::make("Workflow has been updated");
            }
            else
            {
                return \Response::make("No update performed on workflow. See help",500);
            }
            
        }
        else
        {
            return \Response::make("Context not supported",500);
        }
    }

}