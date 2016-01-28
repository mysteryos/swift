<?php

Namespace Swift\Services;

Use \SwiftNodeActivity;
Use \SwiftNodeDefinition;
Use \SwiftWorkflowActivity;
Use \SwiftWorkflowType;
Use \Sentry;

class WorkflowActivity {

    /*
     * Create Workflow Activity In Database
     *
     * @param \Illuminate\Database\Eloquent\Model $relation_object
     * @param string $workflow_name
     *
     * @return boolean
     */

    public $user_id;

    public function __construct()
    {
        $this->user_id = \Helper::getUserId();
        $this->nodeActivity = new NodeActivity;
    }

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

        if(!$this->nodeActivity->hasStartAccess($workflow_name))
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
     * Update Workflow Activity & Node Activity Tables
     *
     * @param \Illuminate\Database\Eloquent\Model $relation_object
     * @param string|boolean $workflow_name
     *
     * @return boolean
     */

    public function update($relation_object,$workflow_name=false)
    {
        //Check if relation is indeed an object
        if(!is_object($relation_object))
        {
            throw new \RuntimeException("Data type 'object' expected.");
        }

        //Control Updates of workflows
        if(!\Config::get('website.workflow_update',true))
        {
            return false;
        }

        $this->nodeActivity->user_id = $this->user_id;

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

            //Check if workflow is complete
            $endNodePresent = (boolean)SwiftNodeActivity::where('workflow_activity_id','=',$workflow->id)
                              ->whereHas('definition',function($q){
                                  return $q->where('type','=',SwiftNodeDefinition::$T_NODE_END);
                              })
                              ->where('user_id','!=',0,'AND')
                              ->count();

            if($endNodePresent)
            {
                $workflow->status = SwiftWorkflowActivity::COMPLETE;
                $workflow->save();
                \Queue::push('Story@relateTask',array('obj_class'=>get_class($workflow),
                                                     'obj_id'=>$workflow->id,
                                                     'user_id'=>\Config::get('website.system_user_id'),
                                                     'context'=>get_class($relation_object),
                                                     'action'=>\SwiftStory::ACTION_COMPLETE));
            }
            else
            {
                //We have workflow object, get Da Nodes
                $nodeInProgress = $this->nodeActivity->inProgress($workflow->id);
                if(!count($nodeInProgress))
                {
                    $latestNodeBefore = SwiftNodeActivity::getLatestByWorkflow($workflow->id);
                    //Check if Nodes Exists
                    if(count($latestNodeBefore))
                    {
                        $processedNodes = [];
                        foreach($latestNodeBefore as $l)
                        {
                            $l->load('parents');
                            //Check if current node preceeds one that has already been processed
                            //If it is, we skip processing.
                            if(count(array_filter($processedNodes,function($e)use($l){
                                foreach($e->parents as $p)
                                {
                                    $p->load('parentNode');
                                    if($p->parentNode && $p->parentNode->node_definition_id === $l->node_definition_id)
                                    {
                                        return true;
                                    }
                                }
                                return false;
                            })))
                            {
                                $processedNodes[] = $l;
                                continue;
                            }

                            $processedNodes[] = $l;

                            $this->nodeActivity->process($l,SwiftNodeActivity::$FLOW_FORWARD);
                        }
                    }
                    else
                    {
                        //No Nodes, Let's get it starteddd
                        $startNodeDef = \NodeDefinition::getStartNodeDefinition($workflow_name);
                        if($startNodeDef)
                        {
                            $startNodeActivity = $this->nodeActivity->create($workflow->id, $startNodeDef);
                            if($startNodeActivity)
                            {
                                $this->nodeActivity->process($startNodeActivity,SwiftNodeActivity::$FLOW_FORWARD);
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
                        $this->nodeActivity->process($n,SwiftNodeActivity::$FLOW_FORWARD);
                    }
                }
            }

            //Check if workflow is complete
            $endNodePresent = (boolean)SwiftNodeActivity::where('workflow_activity_id','=',$workflow->id)
                              ->whereHas('definition',function($q){
                                  return $q->where('type','=',SwiftNodeDefinition::$T_NODE_END);
                              })
                              ->where('user_id','!=',0,'AND')
                              ->count();

            if($endNodePresent)
            {
                $workflow->status = SwiftWorkflowActivity::COMPLETE;
                $workflow->save();
                \Queue::push('Story@relateTask',array('obj_class'=>get_class($workflow),
                                                     'obj_id'=>$workflow->id,
                                                     'user_id'=>\Config::get('website.system_user_id'),
                                                     'context'=>get_class($relation_object),
                                                     'action'=>\SwiftStory::ACTION_COMPLETE));
            }

            //If Timestamp on workflow Activity changes, Node Activity updates have occured
            //Hence we call pusher to push the update to the UI
            $workflowUpdated = $relation_object->workflow()->first();
            if($workflowUpdated->updated_at !== $updateTimeBefore && is_callable(array($relation_object,"channelName"),true))
            {
                $progress = $this->progress($relation_object);
                $progressHTML = 'Current Step: <span class="'.$progress['status_class'].'">'.$progress['label'].'</span>';
                if(\Config::get('pusher.enabled'))
                {
                    $pusher = new \Pusher(\Config::get('pusher.app_key'), \Config::get('pusher.app_secret'), \Config::get('pusher.app_id'));
                    $pusher->trigger('presence-'.$relation_object->channelName(), 'html-update', array('id'=>'workflow_status','html'=>$progressHTML));
                }

                //Deferred mail send to allow for auto processing of pending nodes - Avoid useless mailing for already completed steps
                //Send Mail For pending Nodes
                $this->nodeActivity->mail($workflowUpdated);
            }
        }

        return true;
    }

    /*
     * Update Task for Laravel Queue
     *
     * @param mixed $job
     * @param array $data
     *
     */
    public function updateTask($job,$data)
    {
        //Control Updates of workflows
        if(!\Config::get('website.workflow_update',true))
        {
            return false;
        }

        if(isset($data['user_id']))
        {
           $this->user_id = $data['user_id'];
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
     * Get Current Progress - Checks on a workflow activity and builds a status string with an array of current pending nodes
     *
     * @param \Illuminate\Database\Model $relation_object
     *
     * @return array
     */

    public function progress($relation_object)
    {
        //Check if relation is indeed an object
        if(!is_object($relation_object))
        {
            throw new \RuntimeException("Data type 'object' expected.");
        }

        if($relation_object instanceof \SwiftWorkflowActivity)
        {
            $workflow = $relation_object;
            $relation_object = $workflow->workflowable;
        }
        else
        {
            $workflow = $relation_object->workflow()->first();
        }

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

        $nodeInProgress = $this->nodeActivity->inProgress($workflow->id);
        if(!count($nodeInProgress))
        {
            //No node in progress, Find latest completed node
            $nodesCompleted = SwiftNodeActivity::getLatestByWorkflow($workflow->id);
            if(count($nodesCompleted))
            {
                foreach($nodesCompleted as $n)
                {
                    $n->load('definition');
                    if($n->definition->type == SwiftNodeDefinition::$T_NODE_END && $n->user_id !== 0)
                    {
                        return array('label'=>$n->definition->label,'status'=>SwiftWorkflowActivity::COMPLETE,'status_class'=>'color-green','definition'=>array($n->definition->id),'definition_obj'=>$n->definition ,'definition_name'=>[$n->definition->name]);
                    }
                }
                //Something's wrong with the workflow. Rerun WorkflowActivity Update
                //\Queue::push('WorkflowActivity@updateTask',array('class'=>get_class($relation_object),'id'=>$relation_object->id,'user_id'=>\Sentry::findUserByLogin(\Config::get('website.system_mail'))->id));
            }
        }
        else
        {
            $label = $definition_array = $definition_array_id = $definition_name_array = array();
            foreach($nodeInProgress as $n)
            {
                $label[] = $n->definition->label;
                $definition_array_id[] = $n->definition->id;
                $definition_array[] = $n->definition;
                $definition_name_array[] = $n->definition->name;
            }

            return array('label'=>implode(" / ",$label),
                        'status'=>SwiftWorkflowActivity::INPROGRESS,
                        'status_class'=>'color-orange',
                        'definition'=>$definition_array_id,
                        'definition_obj'=>$definition_array,
                        'definition_name' => $definition_name_array);
        }

        return array('label'=>"Unknown",'status'=>"unknown",'status_class'=>'color-red');
    }


    /*
     * Provides useful information as to how to move the workflow to the next step - Polls information from NodeDefinition classes mostly
     *
     * @param mixed $relation_object
     * @param boolean $needPermission
     *
     * @return string
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
            return \Response::make("No workflow found :( Contact your administrator",500);
        }

        switch($workflow->status)
        {
            case SwiftWorkflowActivity::COMPLETE:
                return \Response::make("Workflow is complete :)");
                break;
            case SwiftWorkflowActivity::REJECTED:
                return \Response::make("Workflow has been rejected");
                break;
            case SwiftWorkflowActivity::INPROGRESS:
                $nodeInProgress = $this->nodeActivity->inProgress($workflow->id);
                $reasonList = array();
                if(count($nodeInProgress))
                {
                    foreach($nodeInProgress as $n)
                    {
                        $reason = $this->nodeActivity->help($n,$needPermission);
                        if(!is_bool($reason))
                        {
                            $reasonList = array_merge($reasonList,$reason);
                        }
                    }
                    if(!empty($reasonList))
                    {
                        return \Response::make(implode(", ",$reasonList),400);
                    }
                    else
                    {
                        return \Response::make("We have no advice for you at the moment");
                    }
                }
                else
                {
                    return \Response::make("Something's not right here. Contact your administrator",500);
                }
                break;
            default:
                return \Response::make("This workflow doesn't look good. Contact your administrator",500);
        }
    }

    /*
     * Loops through nodes in progress and returns reasons list if any
     *
     * @param mixed $relation_object
     * @param boolean $needPermission
     *
     * @return array|boolean
     */
    public function checkProgress($relation_object)
    {
        //Check if relation is indeed an object
        if(!is_object($relation_object))
        {
            throw new \RuntimeException("Data type 'object' expected.");
        }

        $workflow = $relation_object->workflow()->first();
        if(!count($workflow))
        {
            return false;
        }

        switch($workflow->status)
        {
            case SwiftWorkflowActivity::COMPLETE:
                return true;
                break;
            case SwiftWorkflowActivity::REJECTED:
                return true;
                break;
            case SwiftWorkflowActivity::INPROGRESS:
                $nodeInProgress = $this->nodeActivity->inProgress($workflow->id);
                $reasonList = array();
                if(count($nodeInProgress))
                {
                    foreach($nodeInProgress as $n)
                    {
                        $reason = $this->nodeActivity->help($n,true);
                        if(!is_bool($reason))
                        {
                            $reasonList = array_merge($reasonList,$reason);
                        }
                    }
                    if(!empty($reasonList))
                    {
                        return $reasonList;
                    }
                    else
                    {
                        return true;
                    }
                }
                else
                {
                    return false;
                }
                break;
            default:
                return false;
        }
    }

    /*
     * Cancels a workflow activity. Sets its status to rejected in its table
     *
     * @param mixed $relation_object
     *
     * @return boolean
     */
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
                                                     'user_id'=>$this->user_id,
                                                     'context'=>get_class($relation_object),
                                                     'action'=>\SwiftStory::ACTION_CANCEL));
                return true;
            }
        }

        return false;
    }

    /*
     * Cancels a workflow activity. Sets its status to rejected in its table
     *
     * @param mixed $relation_object
     *
     * @return boolean
     */
    public function complete($relation_object)
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
            $workflow->status = SwiftWorkflowActivity::COMPLETE;
            if($workflow->save())
            {
                \Queue::push('Story@relateTask',array('obj_class'=>get_class($workflow),
                    'obj_id'=>$workflow->id,
                    'user_id'=>$this->user_id,
                    'context'=>get_class($relation_object),
                    'action'=>\SwiftStory::ACTION_COMPLETE));
                return true;
            }
        }

        return false;
    }

    /*
     * Returns list of all nodes pending of workflow type
     *
     * @param string|array $workflowTypes
     *
     * @return boolean|\Illuminate\Support\Collection
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

    /*
     * Gets a list of node activities by workflow type which are late, according their ETA set in node definition table
     *
     * @param string|array $workflowTypes
     *
     * @return boolean|\Illuminate\Support\Collection
     */
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