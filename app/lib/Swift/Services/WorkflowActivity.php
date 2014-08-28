<?php

Namespace Swift\Services;

Use \SwiftNodeActivity;
Use \SwiftNodePermission;
Use \SwiftNodeDefinition;
Use \SwiftWorkflowActivity;
Use \SwiftWorkflowType;

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
            //We have workflow object, get Da Nodes
            $nodeInProgress = \NodeActivity::inProgress($workflow->id);
            if(!count($nodeInProgress))
            {
                //Check if Nodes Exists
                $latestNode = SwiftNodeActivity::getLatestByWorkflow($workflow->id);
                if(count($latestNode))
                {
                    foreach($latestNode as $l)
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
            $latestNode = SwiftNodeActivity::getLatestByWorkflow($workflow->id)->first();
            if(count($latestNode))
            {
                $latestNode->load('definition');
                if($latestNode->definition->type == SwiftNodeDefinition::$T_NODE_END && $latestNode->user_id != 0)
                {
                    $workflow->status = SwiftWorkflowActivity::COMPLETE;
                    $workflow->save();
                }
            }
        }
        
        return true;
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
            throw new \Exception ("Workflow activity not found");
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
                        return array('label'=>$n->definition->label,'status'=>1,'status_class'=>'color-green');
                    }
                }
            }
        }
        else
        {
            $label = array();
            foreach($nodeInProgress as $n)
            {
                $label[] = $n->definition->label;
            }
            return array('label'=>implode(" / ",$label),'status'=>SwiftWorkflowActivity::INPROGRESS,'status_class'=>'color-orange');
        }
        
        return array('label'=>"Unknown",'status'=>"unknown",'status_class'=>'color-red');        
    }
    
}