<?php

Namespace Swift\Services;

Use \SwiftNodeActivity;
Use \SwiftNodeDefinition;
Use \SwiftWorkflowActivity;
Use \SwiftWorkflowType;
Use \Sentry;

class WorkflowActivity {
    
    /*
     * Create Workflow Activity
     * 
     */
    
    public function create($relation_object,$workflow_name)
    {
        //Check if relation is indeed an object
        if(!is_object($relation_object))
        {
            throw new \RuntimeException("Data type 'object' expected.");
        }
        
        $workflowType = SwiftWorkflowType::getByName((string)$workflow_name);
        
        /*
         * Verifying Start Workflow Access
         */
        
        if(!\NodeActivity::hasStartAccess($workflow_name))
        {
            //No Access
            throw new \UnexpectedValueException("User doesn't have access to current operation");
        }
        
        /*
         * Check if workflow already exists
         */
        
        if(count($relation_object->workflow))
        {
            throw new \Exception("Workflow activity already exists");
        }
        
        //Start a new activity
        $wa = new SwiftWorkflowActivity(['workflow_type_id'=>$workflowType->id,'status'=>\SwiftWorkflowActivity::INPROGRESS]);
        
        return $relation_object->workflow()->save($wa);
        
    }
    
    /*
     * Update Workflow Activity & Node Activity
     */
    
    public function update($relation_object,$workflow_name=false)
    {
        //Check if relation is indeed an object
        if(!is_object($relation_object))
        {
            throw new \RuntimeException("Data type 'object' expected.");
        }
        
        //Go Fetch!
        $workflow = $relation_object->workflow()->first();
        if(!count($workflow))
        {
            if(self::create($relation_object,$workflow_name))
            {
                $workflow = $relation_object->workflow()->first();
            }
            else
            {
                throw new \Exception ("Workflow activity failed: Cannot create activity.");
            }
        }
        
        if($workflow->status === \SwiftWorkflowActivity::INPROGRESS)
        {
            $updateTimeBefore = $workflow->updated_at;
            
            //We have workflow object, get Da Nodes
            $nodeInProgress = \NodeActivity::inProgress($workflow->id);
            if(!count($nodeInProgress))
            {
                $latestNodeBefore = SwiftNodeActivity::getLatestByWorkflow($workflow->id);
                //Check if Nodes Exists
                if(count($latestNodeBefore))
                {
                    foreach($latestNodeBefore as $l)
                    {
                        \NodeActivity::process($l,SwiftNodeActivity::$FLOW_FORWARD);
                    }
                }
                else
                {
                    //No Nodes, Let's get it starteddd
                    $startNodeDef = \NodeDefinition::getStartNodeDefinition($workflow_name);
                    if($startNodeDef)
                    {
                        $startNodeActivity = \NodeActivity::create($workflow->id, $startNodeDef);
                        if($startNodeActivity)
                        {
                            \NodeActivity::process($startNodeActivity,SwiftNodeActivity::$FLOW_FORWARD);
                        }
                        else
                        {
                            throw new \Exception ("Workflow activity failed: Unable to save start node.");
                        }
                    }
                    else
                    {
                        throw new \Exception ("Workflow activity failed: No Start node.");
                    }                     
                }
            }
            else
            {
                foreach($nodeInProgress as $n)
                {
                    $n->load('definition');
                    \NodeActivity::process($n,SwiftNodeActivity::$FLOW_FORWARD);
                }
            }

            //Check if workflow is complete
            //Get Latest Node Activity
            $latestNodeAfter = SwiftNodeActivity::getLatestByWorkflow($workflow->id)->first();
            if(count($latestNodeAfter))
            {
                $latestNodeAfter->load('definition');
                if($latestNodeAfter->definition->type == SwiftNodeDefinition::$T_NODE_END && $latestNodeAfter->user_id != 0)
                {
                    $workflow->status = SwiftWorkflowActivity::COMPLETE;
                    $workflow->save();
                    \Story::relate($workflow,\SwiftStory::ACTION_COMPLETE);
                }
            }
            
            //If Timestamp on workflow Activity changes, Node Activity updates have occured
            //Hence we call pusher to push the update to the UI
            if($relation_object->workflow()->first()->updated_at !== $updateTimeBefore && is_callable(array($relation_object,"channelName"),true))
            {
                $progress = $this->progress($relation_object);
                $progressHTML = 'Current Step: <span class="'.$progress['status_class'].'">'.$progress['label'].'</span>';
                if(\Config::get('pusher.enabled'))
                {
                    $pusher = new \Pusher(\Config::get('pusher.app_key'), \Config::get('pusher.app_secret'), \Config::get('pusher.app_id'));
                    $pusher->trigger('presence-'.$relation_object->channelName(), 'html-update', array('id'=>'workflow_status','html'=>$progressHTML));
                }
            }
        }
        
        return true;
    }
    
    public function updateTask($job,$data)
    {
        if(isset($data['user_id']))
        {
            $user = Sentry::findUserById($data['user_id']);

            // Log the user in
            Sentry::login($user, false);
        }
        
        if(isset($data['class']) && isset($data['id']))
        {
            $eloqentClass = new $data['class'];
            $eloquentObject = $eloqentClass::find($data['id']);
            $this->update($eloquentObject);
        }
        else
        {
            throw new \RuntimeException('Eloquent class or object ID missing');
        }
        $job->delete();
    }
    
    /*
     * Get Current Progress
     */
    
    public function progress($relation_object)
    {
        //Check if relation is indeed an object
        if(!is_object($relation_object))
        {
            throw new \RuntimeException("Data type 'object' expected.");
        }
        
        $workflow = $relation_object->workflow()->first();
        if(!count($workflow))
        {
            return array('label'=>"Unknown",'status'=>"unknown",'status_class'=>'color-red');
        }
        
        if($workflow->status == SwiftWorkflowActivity::COMPLETE)
        {
            return array('label'=>'Complete','status'=>SwiftWorkflowActivity::COMPLETE,'status_class'=>'color-green');
        }
        
        if($workflow->status == SwiftWorkflowActivity::REJECTED)
        {
            return array('label'=>'Rejected','status'=>SwiftWorkflowActivity::REJECTED,'status_class'=>'color-red');
        }
        
        $nodeInProgress = \NodeActivity::inProgress($workflow->id);
        if(!count($nodeInProgress))
        {
            //No node in progress, Find latest completed node
            $nodesCompleted = SwiftNodeActivity::getLatestByWorkflow($workflow->id);
            if(count($nodesCompleted))
            {
                foreach($nodesCompleted as $n)
                {
                    $n->load('definition');
                    if($n->definition->type == SwiftNodeDefinition::$T_NODE_END && $n->user_id != 0)
                    {
                        return array('label'=>$n->definition->label,'status'=>SwiftWorkflowActivity::COMPLETE,'status_class'=>'color-green','definition'=>array($n->definition->id),'definition_obj'=>$n->definition);
                    }
                }
                //Something's wrong with the workflow. Rerun WorkflowActivity Update
                \Queue::push('WorkflowActivity@updateTask',array('class'=>get_class($relation_object),'id'=>$relation_object->id,'user_id'=>\Sentry::findUserByLogin(\Config::get('website.system_mail'))->id));
            }
        }
        else
        {
            $label = $definition_array = $definition_array_id = array();
            foreach($nodeInProgress as $n)
            {
                $label[] = $n->definition->label;
                $definition_array_id[] = $n->definition->id;
                $definition_array[] = $n->definition;
            }
            return array('label'=>implode(" / ",$label),'status'=>SwiftWorkflowActivity::INPROGRESS,'status_class'=>'color-orange', 'definition'=>$definition_array_id, 'definition_obj'=>$definition_array);
        }
        
        return array('label'=>"Unknown",'status'=>"unknown",'status_class'=>'color-red');        
    }
    
    
    /*
     * Provides useful information as to how to move the workflow to the next step
     */
    public function progressHelp($relation_object,$needPermission=true)
    {
        //Check if relation is indeed an object
        if(!is_object($relation_object))
        {
            throw new \RuntimeException("Data type 'object' expected.");
        }
        
        $workflow = $relation_object->workflow()->first();
        if(!count($workflow))
        {
            return "No workflow found :( Contact your administrator";
        }
        
        switch($workflow->status)
        {
            case SwiftWorkflowActivity::COMPLETE:
                return "Workflow is complete :)";
                break;
            case SwiftWorkflowActivity::REJECTED:
                return "Workflow has been rejected";
                break;
            case SwiftWorkflowActivity::INPROGRESS:
                $nodeInProgress = \NodeActivity::inProgress($workflow->id);
                $reasonList = array();
                if(count($nodeInProgress))
                {
                    foreach($nodeInProgress as $n)
                    {
                        $reason = \NodeActivity::help($n,$needPermission);
                        if(!is_bool($reason))
                        {
                            $reasonList = array_merge($reasonList,$reason);
                        }
                    }
                    if(!empty($reasonList))
                    {
                        return implode(", ",$reasonList);
                    }
                    else
                    {
                        return "We have no advice for you at the moment";
                    }
                }
                else
                {
                    return "Something's not right here. Contact your administrator";
                }
                break;
            default:
                return "This workflow doesn't look good. Contact your administrator";
        }
    }    
    
    public function cancel($relation_object)
    {
        //Check if relation is indeed an object
        if(!is_object($relation_object))
        {
            throw new \RuntimeException("Data type 'object' expected.");
        }
        
        $workflow = $relation_object->workflow()->first();
        if(!count($workflow))
        {
            throw new \Exception ("Workflow activity not found");
        }
        
        $workflow = $relation_object->workflow()->first();
        if($workflow->status === SwiftWorkflowActivity::INPROGRESS)
        {
            $workflow->status = SwiftWorkflowActivity::REJECTED;
            if($workflow->save())
            {
                \Queue::push('Story@relateTask',array('obj_class'=>get_class($workflow),
                                                     'obj_id'=>$workflow->id,
                                                     'user_id'=>\Sentry::getUser()->id,
                                                     'context'=>get_class($relation_object),
                                                     'action'=>\SwiftStory::ACTION_CANCEL));
                return true;
            }
        }
        
        return false;
    }
    
    /*
     * @parameter workflowTypes: string | array
     * Returns list of all nodes pending of workflow type
     */
    public function statusByType($workflowTypes)
    {
        $nodeActivities = \SwiftNodeActivity::getPendingNodes($workflowTypes);
        if(count($nodeActivities))
        {
            foreach($nodeActivities as &$n)
            {
                $permissions = \SwiftNodePermission::where('node_definition_id','=',$n->node_definition_id)
                                ->where('permission_type','=',\SwiftNodePermission::RESPONSIBLE,'AND')
                                ->select('permission_name')->distinct()->get()->toArray();
                if(count($permissions))
                {
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
            return $nodeActivities;
        }
        
        return false;
    }
    
    public function lateNodeByForm($workflowTypes)
    {
        $nodeActivities = \SwiftNodeActivity::getLateNodes($workflowTypes);
        if(count($nodeActivities))
        {
            foreach($nodeActivities as $k => &$activity)
            {
                if(count($activity->permission))
                {
                    $permissions = $activity->permission->toArray();
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
                        $activity->users = $users;
                    }
                }
                
                $activity->dueSince = \Helper::nextBusinessDay($activity->updated_at->addDays($activity->definition->eta));
            }
            
            if(!$nodeActivities->isEmpty())
            {
                return $nodeActivities;
            }
        }
        return false;
    }
    
}