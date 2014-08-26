<?php
/* 
 * Name: Node Activity
 */

Namespace Swift\Services;

Use \SwiftNodeActivity;
Use \SwiftNodeActivityJoin;
Use \SwiftNodePermission;
Use \SwiftNodeDefinition;
Use \SwiftNodeDefinitionJoin;
Use \SwiftWorkflowActivity;
Use \SwiftWorkflowType;
Use \Sentry;
Use \NodeDefinition;

class NodeActivity {
    
    /*
     * Fetches all nodes which are currently in progress
     */
    public function inProgress($workflow_activity_id)
    {
        $nodes = SwiftNodeActivity::inProgress($workflow_activity_id);
        return $nodes;
    }
    
    /*
     * Create node activity
     */
    public function create($workflow_activity_id,$node_definition)
    {
        $nodeCheck = SwiftNodeActivity::where('node_definition_id','!=',$node_definition->id)->get();
        //If node with same definition doesn't exists
        if(!count($nodeCheck))
        {
            $nodeActivity = new SwiftNodeActivity;
            $nodeActivity->node_definition_id = $node_definition->id;
            $nodeActivity->workflow_activity_id = $workflow_activity_id;
            if($nodeActivity->save())
            {
                return $nodeActivity;
            }
        }
        
        return false;
    }
    
    /*
     * Joins activities
     */
    public function join($parent_id,$child_id)
    {
        $nodeJoin = new SwiftNodeActivityJoin;
        $nodeJoin->parent_id = $parent_id;
        $nodeJoin->children_id = $child_id;
        return $nodeJoin->save();
    }
    
    
    /*
     * Save Current Node
     */
    public function save($node_activity,$flow=1/*Forward Flow*/)
    {
        $node_activity->user_id = Sentry::getUser()->id;
        $node_activity->flow = $flow;
        $node_activity->save();
    }
    
    /*
     * Process Current Node - Evaluate Criteria and Conditions
     * Expects Node with user id = 0
     */
    public function process($nodeActivity,$flow)
    {
        if(!is_object($nodeActivity) && is_numeric($nodeActivity))
        {
            $nodeActivity = SwiftNodeActivity::with('definition')->find($nodeActivity);
        }
        
        /*
         * Node Not found
         */
        if(!count($nodeActivity))
        {
            throw new \UnexpectedValueException("Node Activity: Expected node not found");
        }
        
        /*
         * Check if user has access to node
         */
        if(!self::hasAccess($nodeActivity->definition->id))
        {
            return array('success'=>0,'msg'=>"You don't have access to this action");
        }
        
        /*
         * Check if node activity has been processed
         */
        if($nodeActivity->user_id != 0)
        {
            return true;
        }
        
        switch($nodeActivity->definition->type)
        {
            case SwiftNodeDefinition::$T_NODE_START:
                if($flow !=SwiftNodeActivity::$FLOW_BACKWARD)
                {
                    /*
                     * Call Utility Function
                     */
                    $function = $nodeActivity->definition->php_function."::".lcfirst(studly_case($nodeActivity->definition->name));                    
                    if(is_callable($function))
                    {
                        call_user_func_array($function,array($nodeActivity));
                    }
                    
                    self::save($nodeActivity,$flow);
                    /*
                     * As per flow, create other nodes
                     */
                    switch($flow)
                    {
                        case SwiftNodeActivity::$FLOW_FORWARD:
                            self::goNext($nodeActivity);
                            break;
                        case SwiftNodeActivity::$FLOW_BACKWARD:
                            self::goPrevious($nodeActivity);
                            break;
                        case SwiftNodeActivity::$FLOW_STOP:
                            //Ain't gonna go anywhere
                            break;
                        default:
                            break;
                    }
                }
                else
                {
                    throw new \RuntimeException("Start node should not flow backwards.");
                }
                break;
            case SwiftNodeDefinition::$T_NODE_END:
                
                /*
                 * Call Utility Function
                 */
                $function = $nodeActivity->definition->php_function."::".lcfirst(studly_case($nodeActivity->definition->name));                    
                if(is_callable($function))
                {
                    call_user_func_array($function,array($nodeActivity));
                }                
                
                //Save Current
                self::save($nodeActivity,SwiftNodeActivity::$FLOW_STOP);
                break;
            case SwiftNodeDefinition::$T_NODE_CONDITION:                
            case SwiftNodeDefinition::$T_NODE_INPUT:
            case SwiftNodeDefinition::$T_NODE_ACTION:
                $function = $nodeActivity->definition->php_function."::".lcfirst(studly_case($nodeActivity->definition->name));
                if(is_callable($function))
                {
                    /*
                     * If function returns true
                     */
                    if(call_user_func_array($function,array($nodeActivity)))
                    {
                        //Save Current
                        self::save($nodeActivity,$flow);
                        /*
                         * As per flow, create other nodes
                         */
                        switch($flow)
                        {
                            case SwiftNodeActivity::$FLOW_FORWARD:
                                self::goNext($nodeActivity);
                                break;
                            case SwiftNodeActivity::$FLOW_BACKWARD:
                                self::goPrevious($nodeActivity);
                                break;
                            case SwiftNodeActivity::$FLOW_STOP:
                                //Ain't gonna go anywhere
                                break;
                            default:
                                break;
                        }
                    }
                    else
                    {
                        return false;
                    }
                }
                else
                {
                    throw new \RuntimeException("Condition node requires callable function");
                }
                break;
            case SwiftNodeDefinition::$T_NODE_KILL:
                //Save Current
                self::save($nodeActivity,SwiftNodeActivity::$FLOW_STOP);                
                break;
            default:
                throw new \UnexpectedValueException("Type of node activity is unknown");
        }
        
        return true;
        
    }
    
    /*
     * Helper Function: Forward Flow Processing
     */
    public function processForward($node_activity_id)
    {
        return self::process($node_activity_id, SwiftNodeActivity::$FLOW_FORWARD);
    }
    
    /*
     * Helper Function: Backward Flow Processing
     */
    public function processBackward($node_activity_id)
    {
        return self::process($node_activity_id, SwiftNodeActivity::$FLOW_BACKWARD);
    }
    
    /*
     * Evaluates Branching & Creates next nodes
     */
    
    public function goNext($currentNodeActivity)
    {
        $insertedNodesArray = array();
        //Get Next Nodes
        $nextNodes = SwiftNodeDefinitionJoin::getByParent($currentNodeActivity->node_definition_id);
        if(count($nextNodes))
        {
            switch($nextNodes->first()->pattern)
            {
                case SwiftNodeDefinitionJoin::$P_SEQUENCE:
                    //Sequence = Single node branch
                    if(count($nextNodes) == 1)
                    {
                        //Fetch children
                        $nextNodeDefinition = SwiftNodeDefinition::find($nextNodes->first()->children_id);
                        if(count($nextNodeDefinition))
                        {
                            $newNodeActivity = self::create($currentNodeActivity->workflow_activity_id,$nextNodeDefinition);
                            if($newNodeActivity)
                            {
                                $insertedNodesArray[] = $newNodeActivity;
                                self::join($currentNodeActivity->id,$newNodeActivity->id);
                            }
                            else
                            {
                                throw new \Exception("Node Activity: Failed to create one");
                            }
                        }
                        else
                        {
                            throw new \Exception("Node Activity: Failed to find node definition");
                        }
                    }
                    else
                    {
                        throw new \RuntimeException("Node Activity: Node with pattern 'sequence' has more than one branch");
                    }
                    break;
                case SwiftNodeDefinitionJoin::$P_AND_SPLIT:
                case SwiftNodeDefinitionJoin::$P_XOR_SPILT:
                    //No conditions to evaluate - Straight Forward Creation of Nodes
                    //Expected count more than 1
                    if(count($nextNodes) > 1)
                    {
                        /*
                         * Loop through node definitions
                         */
                        foreach($nextNodes as $n)
                        {
                            $newNodeActivity = self::create($currentNodeActivity->workflow_activity_id,$n->childNode);
                            if($newNodeActivity)
                            {
                                $insertedNodesArray[] = $newNodeActivity;
                                self::join($currentNodeActivity->id,$newNodeActivity->id);
                            }
                            else
                            {
                                throw new \Exception("Node Activity: Failed to create one");
                            }                                
                        }
                    }
                    else
                    {
                        throw new \RuntimeException("Node of 'and split' must have more than one branch");
                    }
                    break;
                case SwiftNodeDefinitionJoin::$P_AND_JOIN:
                    //Verify Condition before joining nodes
                    //Lazy Check - If first condition evaluates to false, no need to verify others.
                    $function = $nextNodes->first()->php_function."::".lcfirst(studly_case($nextNodes->first()->name));
                    if(is_callable($function))
                    {
                        if(call_user_func_array($function,array($currentNodeActivity)))
                        {
                            //Check if other nodes have met their conditions
                            $andJoinNodeId = $nextNodes->first()->children_id;
                            /*
                             * Get all nodes associated with the node being joined to with "and Joins"
                             */
                            $nodesWithAndJoin = SwiftNodeDefinitionJoin::getByChild($andJoinNodeId);
                            $conditionAnd = true;
                            if(count($nodesWithAndJoin) < 2)
                            {
                                throw new UnexpectedValueExemption("An \"And branch\" terminated wtih a single branch; Multiple branches expected");
                            }
                            /*
                             * Loop through all Node Branching Conditions & Check them
                             */
                            foreach($nodesWithAndJoin as $n)
                            {
                                //Check if parent node activity exists first
                                $parentNodeActivity = SwiftNodeActivity::getByWorkflowAndDefinition($currentNodeActivity->workflow_activity_id, $n->parent_node->id);
                                if(count($parentNodeActivity))
                                {
                                    /*
                                     * Push IDs to an array for later use
                                     */
                                    $parentNodeIds[] = $parentNodeActivity->id;
                                    /*
                                     * If exists, we check branching condition
                                     */
                                    $func = $n->php_function."::".studly_case($n->name);
                                    if(is_callable($func))
                                    {
                                        if(!call_user_func_array($func,array($parentNodeActivity)))
                                        {
                                            $conditionAnd = false;
                                            break;
                                        }
                                        else
                                        {
                                            throw new \RuntimeException("Function '{$func}' is not callable.");
                                        }                                        
                                    }
                                }
                                else
                                {
                                    /*
                                     * Parent Node Not found
                                     * It means that the parallel process has not reached up to this node yet.
                                     */
                                    $conditionAnd = false;
                                    break;                                    
                                }
                            }
                            
                            /*
                             * Conditions couldn't be more ideal.
                             */
                            if($conditionAnd)
                            {
                                /*
                                 * Node is expected to be solely one.
                                 */
                                $insertedNode = self::create($currentNodeActivity->workflow_activity_id,$nextNodes->childNode);
                                if($insertedNode)
                                {
                                    $insertedNodesArray[] = $insertedNode;
                                    /*
                                     * Joins are expected to be two or more
                                     * Loop through ParentNodeIDs and Join the Nodes
                                     */

                                    foreach($parentNodeIds as $parentId)
                                    {
                                        self::join($parentId,$insertedNode->id);
                                    }
                                }
                            }
                        }
                    }
                    else
                    {
                        throw new \RuntimeException("Function '{$function}' is not callable.");
                    }                     
                    break;
                case SwiftNodeDefinitionJoin::$P_XOR_JOIN:
                    //Verify Condition
                    //Lazy Check - If first condition evaluates to true, no need to verify others.
                    $function = $nextNodes->first()->php_function."::".studly_case($nextNodes->first()->name);
                    if(is_callable($function))
                    {
                        if(!call_user_func_array($function,array($currentNodeActivity)))
                        {
                            //Check if other nodes have met their conditions
                            $orJoinNodeId = $nextNodes->first()->children_id;
                            /*
                             * Get all nodes associated with the node being joined to with "and Joins"
                             */
                            $nodesWithOrJoin = SwiftNodeDefinitionJoin::getByChild($orJoinNodeId);
                            $conditionOr = true;
                            if(count($nodesWithOrJoin) < 2)
                            {
                                throw new UnexpectedValueExemption("An \"And branch\" terminated wtih a single branch; Multiple branches expected");
                            }
                            /*
                             * Loop through all Node Branching Conditions & Check them
                             */
                            $conditionOr = false;
                            foreach($nodesWithOrJoin as $n)
                            {
                                //Check if parent node activity exists first
                                $parentNodeActivity = SwiftNodeActivity::getByWorkflowAndDefinition($currentNodeActivity->workflow_activity_id, $n->parent_node->id);
                                if($parentNodeActivity)
                                {
                                    /*
                                     * If exists, we check branching condition
                                     */
                                    $func = $n->php_function."::".studly_case($n->name);
                                    if(is_callable($func))
                                    {
                                        if(call_user_func_array($func,array($parentNodeActivity)))
                                        {
                                            $conditionOr = true;
                                            break;
                                        }
                                    }
                                    else
                                    {
                                        throw new \RuntimeException("Function '{$func}' is not callable.");
                                    }
                                }
                            }
                        }
                        else
                        {
                            $conditionOr = true;
                        }
                            
                        /*
                         * Conditions couldn't be more ideal.
                         */
                        if($conditionOr)
                        {
                            /*
                             * Node is expected to be solely one.
                             */
                            $insertedNode = self::create($currentNodeActivity->workflow_activity_id,$nextNodes->childNode);
                            if($insertedNode)
                            {
                                $insertedNodesArray[] = $insertedNode;
                                /*
                                 * Joins are expected to be only one.
                                 * Loop through ParentNodeIDs and Join the Nodes
                                 */
                                self::join($parentNodeActivity->id,$insertedNode->id);
                            }
                        }                        
                    }
                    else
                    {
                        throw new \RuntimeException("Function '{$function}' is not callable.");
                    }                    
                    break;
//                case SwiftNodeDefinition::$P_OR_SPLIT:
//                    break;
//                case SwiftNodeDefinition::$P_OR_JOIN:
                    //Verify Condition
                    break;
                default:
                    throw new \UnexpectedValueException("Node definition pattern is unknown.");
            }
            if(!empty($insertedNodesArray))
            {
                foreach($insertedNodesArray as $i)
                {
                    self::processForward($i->load('definition'));
                }
            }
        }
        else
        {
            throw new \RuntimeException("NodeActivity: Expected child nodes, None found");
        }
    }
    
    /*
     * Visits previous node in workflow
     */
    public function goPrevious($currentNodeActivity)
    {
            
    }
    
    /*
     * Verfiy if user has access to this node
     */
    public function hasAccess($node_definition_id)
    {
        //Super Man Access
        if(Sentry::getUser()->isSuperUser())
        {
            return true;
        }
        
        $nodeDefinition = SwiftNodeDefinition::find($node_definition_id)->permission()->first();
        if(count($nodeDefinition) && count($nodeDefinition->permission))
        {
            $arrayPermission = array_map(function($p){
                return $p->permission_name;
            },$nodeDefinition->permission);
            return Sentry::getUser()->hasAnyAccess((array)$arrayPermission);
        }
        
        return false;
    }
    
    /*
     * Helper Function: has Start node Access
     */
    public function hasStartAccess($workflow_name)
    {
        //Get Start node
        
        $nodeDefinition = NodeDefinition::getStartNodeDefinition($workflow_name);
        
        if(count($nodeDefinition))
        {
            return self::hasAccess($nodeDefinition->id);
        }
        else
        {
            return false;
        }
    }
    
    /*
     * Helper Function: has Kill node Access
     */
    public function hasKillAccess($workflow_name)
    {
        //Get Kill node
        
        $workflow = SwiftWorkflowType::getByName($workflow_name);
        
        if(count($workflow))
        {
            $nodeDefinition = SwiftNodeDefinition::getByType($workflow->id,SwiftNodeDefinition::$T_NODE_KILL)->first();

            return self::hasAccess($nodeDefinition->id);            
        }
        else
        {
            throw New \RuntimeException ("Workflow of name '{$workflow_name}' Not Found");
            return false;
        }

    }
    
    /*
     * Helper Function: has End node Access
     */
    public function hasEndAccess($workflow_name)
    {
        //Get End Node
        
        $workflow = SwiftWorkflowType::getByName($workflow_name);
        
        if(count($workflow))
        {        
            $nodeDefinition = SwiftNodeDefinition::getByType($workflow->id,SwiftNodeDefinition::$T_NODE_END)->first();

            return self::hasAccess($nodeDefinition->id);
        }
        else
        {
            throw New \RuntimeException ("Workflow of name '{$workflow_name}' Not Found");
            return false;            
        }
    }
    
    /*
     * Helper Function: Fetches Start Node Activty
     */
    public function getStartNodeActivity($workflow_name)
    {
        $workflow = SwiftWorkflowType::getByName($workflow_name);
        
        if(count($workflow))
        {
            return SwiftNodeActivity::getByWorkflowAndDefinition($workflow->id,SwiftNodeDefinition::$T_NODE_START)->first();
        }
        else
        {
            throw New \RuntimeException ("Workflow of name '{$workflow_name}' Not Found");
        }        
    }
    
}