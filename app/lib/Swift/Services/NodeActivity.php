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
Use \SwiftWorkflowType;
Use \Sentry;
Use \NodeDefinition;

class NodeActivity {

    public $user_id;

    public function __construct()
    {
        $this->user_id = \Helper::getUserId();
    }

    /*
     * Fetches all nodes which are currently in progress
     *
     * @param integer $workflow_activity_id
     *
     * @return \Illuminate\Support\Collection
     */
    public function inProgress($workflow_activity_id)
    {
        $nodes = SwiftNodeActivity::inProgress($workflow_activity_id);
        return $nodes;
    }

    /*
     * Checks if mail has been sent to resposible parties for current pending nodes.
     * If it hasn't, send mail and flag `mailed` column in node activity table
     *
     * @param \SwiftWorkflowActivity $workflow_activity
     *
     */
    public function mail(\SwiftWorkflowActivity $workflow_activity)
    {
        $nodeActivityInProgress = SwiftNodeActivity::where('workflow_activity_id','=',$workflow_activity->id)
                                  ->inprogress()
                                  ->with(['definition','definition.permission'=>function($q){
                                      return $q->responsible();
                                  }])
                                  ->get();
        if(count($nodeActivityInProgress))
        {
            foreach($nodeActivityInProgress as $n)
            {
                //Get list of permissions
                if(count($n->definition->permission))
                {
                    $permissionArray = array_map(function($v){
                                            return $v['permission_name'];
                                        },$n->definition->permission->toArray());
                    //send mail
                    if($n->mailed === 0 && $n->definition->php_mail_function !== "" && $n->definition->php_mail_function !== null)
                    {
                        if(is_callable($n->definition->php_mail_function."::sendMail"))
                        {
                            call_user_func_array($n->definition->php_mail_function."::sendMail",array($workflow_activity,$permissionArray));
                            $n->mailed = 1;
                        }
                        else
                        {
                            throw new \Exception("Mail php function '{$n->definition->php_mail_function}::sendMail' is not callable",E_USER_ERROR);
                        }
                    }
                    //send Notification
                    if($n->notified === 0)
                    {
                        if($n->definition->php_channel_name !== "")
                        {
                            if(is_callable("\Channel\\{$workflow_activity->workflowable_type}::triggerByName") && $n->definition->php_channel_name)
                            {
                                $channelClass = "\Channel\\".$workflow_activity->workflowable_type;
                                $channel = new $channelClass($workflow_activity->workflowable,$n->definition->php_channel_name);
                                $channel->triggerByName(['nodeActivity'=>$n]);
                            }
                        }
                        if($n->definition->php_notification_function !== "" && $n->definition->php_notification_function !== null)
                        {
                            if(is_callable($n->definition->php_notification_function."::sendNotification"))
                            {
                                call_user_func_array($n->definition->php_notification_function."::sendNotification",array($workflow_activity,$permissionArray));
                                $n->notified = 1;
                            }
                        }
                    }
                    $n->save();
                }
            }
        }
    }
    
    /*
     * Insert node activity record in its table
     *
     * @param integer $workflow_activity_id
     * @param \SwiftNodeDefinition $node_definition
     *
     * @return boolean|\Illuminate\Database\Eloquent\Model
     */
    public function create($workflow_activity_id,SwiftNodeDefinition $node_definition)
    {
        $nodeCheck = SwiftNodeActivity::countByWorkflowAndDefinitionPending($workflow_activity_id,$node_definition->id);
        //If node with same definition doesn't exists and is pending
        if($nodeCheck === 0)
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
     * Add a join record for a parent and its child by their IDs in a table
     *
     * @param integer $parent_id
     * @param integer $child_id
     *
     * @return boolean
     */
    public function join($parent_id,$child_id)
    {
        $nodeJoin = new SwiftNodeActivityJoin;
        $nodeJoin->parent_id = $parent_id;
        $nodeJoin->children_id = $child_id;
        return $nodeJoin->save();
    }
    
    
    /*
     * Saves a complete node into the table. A node is completed when its column `user_id` is not zero.
     *
     * @param \SwiftNodeActivity $node_activity
     * @param integer $flow
     *
     */
    public function save(SwiftNodeActivity $node_activity,$flow=1/*Forward Flow*/)
    {
        $node_activity->user_id = $this->user_id;
        $node_activity->flow = $flow;
        $node_activity->save();
    }
    
    /*
     * Return help text for current pending node
     *
     * @param \Illumminate\Database\Eloquent\Model $nodeActivity
     * @param boolean $needPermission
     *
     * @return boolean|string
     */
    
    public function help($nodeActivity,$needPermission = true)
    {
        /*
         * Node Not found
         */
        if(!count($nodeActivity))
        {
            throw new \UnexpectedValueException("Node Activity: Expected node not found");
        }
        
        if($needPermission)
        {
            /*
             * Check if user has access to node
             */
            if(!self::hasAccess($nodeActivity->definition->id))
            {
                return false;
            }
        }
        
        switch($nodeActivity->definition->type)
        {
            case SwiftNodeDefinition::$T_NODE_CONDITION:
            case SwiftNodeDefinition::$T_NODE_INPUT:
            case SwiftNodeDefinition::$T_NODE_ACTION:
                $function = $nodeActivity->definition->php_function."::".lcfirst(studly_case($nodeActivity->definition->name));
                if(is_callable($function))
                {
                    /*
                     * If function returns true
                     */
                    $helpReason = call_user_func_array($function,array($nodeActivity,true));
                    return $helpReason;
                }
                break;
            default:
                return false;
        }
    }
    
    /*
     * Process Current Node - Evaluate Criteria and Conditions
     * Expects Node with user id = 0
     *
     * @param integer|\Illuminate\Database\Eloquent\Model $nodeActivity
     * @param integer $flow
     *
     * @return boolean
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
        
        switch($nodeActivity->definition->type)
        {
            //Start of a workflow. It always starts with a node definition of type $T_NODE_START
            case SwiftNodeDefinition::$T_NODE_START:
                if($flow !=SwiftNodeActivity::$FLOW_BACKWARD)
                {
                    /*
                     * Check if node activity has been processed
                     */
                    if($nodeActivity->user_id == 0)
                    {
                        /*
                         * Call Utility Function
                         */
                        $function = $nodeActivity->definition->php_function."::".lcfirst(studly_case($nodeActivity->definition->name));                    
                        if(is_callable($function))
                        {
                            call_user_func_array($function,array($nodeActivity,false));
                        }
                        
                        self::save($nodeActivity,$flow);
                    }
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
            //When a workflow ends, it always ends with a node with definition $T_NODE_END
            case SwiftNodeDefinition::$T_NODE_END:
                /*
                 * Check if node activity has been processed
                 */
                if($nodeActivity->user_id == 0)
                {
                    /*
                     * Call Utility Function
                     */
                    $function = $nodeActivity->definition->php_function."::".lcfirst(studly_case($nodeActivity->definition->name));                    
                    if(is_callable($function))
                    {
                        call_user_func_array($function,array($nodeActivity,false));
                    }                
                
                    //Save Current
                    self::save($nodeActivity,SwiftNodeActivity::$FLOW_STOP);
                }
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
                    if(call_user_func_array($function,array($nodeActivity,false)))
                    {
                        /*
                         * Check if node activity has been processed
                         */
                        if($nodeActivity->user_id == 0)
                        {
                            //Save Current
                            self::save($nodeActivity,$flow);
                            //Story Relate
                            //If Notification has to be sent
                            if($nodeActivity->definition->php_notification_function)
                            {
                                \Story::relate($nodeActivity,\SwiftStory::ACTION_COMPLETE);
                            }
                            //Send Notification of Success
                            \Notification::send(\SwiftNotification::TYPE_SUCCESS,$nodeActivity);
                        }
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
     *
     * @param integer $node_activity_id
     *
     * @return boolean
     */
    public function processForward($node_activity_id)
    {
        return self::process($node_activity_id, SwiftNodeActivity::$FLOW_FORWARD);
    }
    
    /*
     * Helper Function: Backward Flow Processing
     * Note: Never used.
     *
     * @param integer $node_activity_id
     *
     * @return boolean
     */
    public function processBackward($node_activity_id)
    {
        return self::process($node_activity_id, SwiftNodeActivity::$FLOW_BACKWARD);
    }
    
    /*
     * Evaluates Branching Conditions & Creates next nodes (recursively if needed)
     * All branching conditions for the switch statement are explained in \SwiftNodeDefinitionJoin
     * Each branch will evaluate its conditions by calling the function of the class stated in the table swift_node_definition, column `php_function`.
     * If evaluate to true, then we flag the node as complete by inserting the `user_id` column with the current user's id.
     * Then we move to creating the next nodes in the workflow, if our branching allows us to.
     *
     * @param \SwiftNodeActivity $currentNodeActivity
     *
     */
    
    public function goNext(SwiftNodeActivity $currentNodeActivity)
    {
        $insertedNodesArray = array();
        //Get Next Node Definitions
        $nextNodeDefinitionJoins = SwiftNodeDefinitionJoin::getByParent($currentNodeActivity->node_definition_id);
        if(count($nextNodeDefinitionJoins))
        {
            switch($nextNodeDefinitionJoins->first()->pattern)
            {
                case SwiftNodeDefinitionJoin::$P_SEQUENCE:
                    //Sequence = Single node branch
                    if(count($nextNodeDefinitionJoins) == 1)
                    {
                        $function = $nextNodeDefinitionJoins->first()->php_function."::".lcfirst(studly_case($nextNodeDefinitionJoins->first()->name));
                        /*
                         * Call function if possible
                         */
                        if(is_callable($function))
                        {
                            if(call_user_func_array($function,array($currentNodeActivity,false)))
                            {
                                //Fetch children
                                $nextNodeDefinition = $nextNodeDefinitionJoins->first()->childNode;
                                if(count($nextNodeDefinition))
                                {
                                    $newNodeActivity = self::create($currentNodeActivity->workflow_activity_id,$nextNodeDefinition);
                                    if($newNodeActivity)
                                    {
                                        $insertedNodesArray[] = $newNodeActivity;
                                        self::join($currentNodeActivity->id,$newNodeActivity->id);
                                    }
                                }
                                else
                                {
                                    throw new \Exception("Node Activity: Failed to find node definition");
                                }                                
                            }
                        }
                        else
                        {
                            /*
                             * If there is no function, we continue ahead
                             */
                            //Fetch children
                            $nextNodeDefinition = $nextNodeDefinitionJoins->first()->childNode;
                            if(count($nextNodeDefinition))
                            {
                                $newNodeActivity = self::create($currentNodeActivity->workflow_activity_id,$nextNodeDefinition);
                                if($newNodeActivity)
                                {
                                    $insertedNodesArray[] = $newNodeActivity;
                                    self::join($currentNodeActivity->id,$newNodeActivity->id);
                                }
                            }
                            else
                            {
                                throw new \Exception("Node Activity: Failed to find node definition");
                            }                       
                        }
                    }
                    else
                    {
                        throw new \RuntimeException("Node Activity: Node with pattern 'sequence' has more than one branch");
                    }
                    break;
                case SwiftNodeDefinitionJoin::$P_AND_SPLIT:
                case SwiftNodeDefinitionJoin::$P_OR_SPLIT:
                    //No conditions to evaluate - Straight Forward Creation of Nodes
                    //Expected count more than 1
                    if(count($nextNodeDefinitionJoins) > 1)
                    {
                        /*
                         * Loop through node definitions
                         */
                        foreach($nextNodeDefinitionJoins as $n)
                        {
                            $newNodeActivity = self::create($currentNodeActivity->workflow_activity_id,$n->childNode);
                            if($newNodeActivity)
                            {
                                $insertedNodesArray[] = $newNodeActivity;
                                self::join($currentNodeActivity->id,$newNodeActivity->id);
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
                    $function = $nextNodeDefinitionJoins->first()->php_function."::".lcfirst(studly_case($nextNodeDefinitionJoins->first()->name));
                    if(is_callable($function))
                    {
                        if(call_user_func_array($function,array($currentNodeActivity,false)))
                        {
                            //Check if other nodes have met their conditions
                            $andJoinNodeId = $nextNodeDefinitionJoins->first()->children_id;
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
                                //Check if parent node activity exists first and is complete
                                $parentNodeActivity = SwiftNodeActivity::getByWorkflowAndDefinition($currentNodeActivity->workflow_activity_id, $n->parent_node->id);
                                if(count($parentNodeActivity) && (int)$parentNodeActivity->user_id != 0)
                                {
                                    /*
                                     * Push IDs to an array for later use
                                     */
                                    $parentNodeIds[] = $parentNodeActivity->id;
                                    /*
                                     * If exists, we check branching condition
                                     */
                                    $func = $n->php_function."::".lcfirst(studly_case($n->name));
                                    if(is_callable($func))
                                    {
                                        if(!call_user_func_array($func,array($parentNodeActivity,false)))
                                        {
                                            $conditionAnd = false;
                                            break;
                                        }
                                    }
                                    else
                                    {
                                        throw new \RuntimeException("Function '{$func}' is not callable.");
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
                                $insertedNode = self::create($currentNodeActivity->workflow_activity_id,$nextNodeDefinitionJoins->first()->childNode);
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
                case SwiftNodeDefinitionJoin::$P_XAND_JOIN:
                    //Verify Condition before joining nodes
                    //Lazy Check - If first condition evaluates to false, no need to verify others.
                    $function = $nextNodeDefinitionJoins->first()->php_function."::".lcfirst(studly_case($nextNodeDefinitionJoins->first()->name));
                    if(is_callable($function))
                    {
                        if(call_user_func_array($function,array($currentNodeActivity,false)))
                        {
                            //Check if other nodes have met their conditions
                            $andJoinNodeId = $nextNodeDefinitionJoins->first()->children_id;
                            /*
                             * Get all nodes associated with the node being joined to with "and Joins"
                             */
                            $nodesWithAndJoin = SwiftNodeDefinitionJoin::getByChild($andJoinNodeId);
                            $conditionAnd = true;
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
                                    $func = $n->php_function."::".lcfirst(studly_case($n->name));
                                    if(is_callable($func))
                                    {
                                        if(!call_user_func_array($func,array($parentNodeActivity,false)))
                                        {
                                            $conditionAnd = false;
                                            break;
                                        }
                                    }
                                    else
                                    {
                                        throw new \RuntimeException("Function '{$func}' is not callable.");
                                    }                                     
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
                                $insertedNode = self::create($currentNodeActivity->workflow_activity_id,$nextNodeDefinitionJoins->first()->childNode);
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
                case SwiftNodeDefinitionJoin::$P_OR_JOIN:
                case SwiftNodeDefinitionJoin::$P_XOR_JOIN:
                    //Verify Condition
                    //Lazy Check - If first condition evaluates to true, no need to verify others.
                    $function = $nextNodeDefinitionJoins->first()->php_function."::".studly_case($nextNodeDefinitionJoins->first()->name);
                    if(is_callable($function))
                    {
                        if(!call_user_func_array($function,array($currentNodeActivity,false)))
                        {
                            //Check if other nodes have met their conditions
                            $orJoinNodeId = $nextNodeDefinitionJoins->first()->children_id;
                            /*
                             * Get all nodes associated with the node being joined to with "and Joins"
                             */
                            $nodesWithOrJoin = SwiftNodeDefinitionJoin::getByChild($orJoinNodeId);
                            $conditionOr = true;
                            if(count($nodesWithOrJoin) < 2)
                            {
                                throw new \UnexpectedValueExemption("An \"Or branch\" terminated wtih a single branch; Multiple branches expected");
                            }
                            /*
                             * Loop through all Node Branching Conditions & Check them
                             */
                            $conditionOr = false;
                            foreach($nodesWithOrJoin as $n)
                            {
                                //Check if parent node activity exists first
                                $parentNodeActivity = SwiftNodeActivity::getByWorkflowAndDefinition($currentNodeActivity->workflow_activity_id, $n->parent_node->id);
                                if(count($parentNodeActivity) && (int)$parentNodeActivity->user_id != 0)
                                {
                                    /*
                                     * If exists, we check branching condition
                                     */
                                    $func = $n->php_function."::".lcfirst(studly_case($n->name));
                                    if(is_callable($func))
                                    {
                                        if(call_user_func_array($func,array($parentNodeActivity,false)))
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
                            $insertedNode = self::create($currentNodeActivity->workflow_activity_id,$nextNodeDefinitionJoins->first()->childNode);
                            if($insertedNode)
                            {
                                $insertedNodesArray[] = $insertedNode;
                                /*
                                 * Joins are expected to be only one.
                                 * Loop through ParentNodeIDs and Join the Nodes
                                 */
                                self::join($currentNodeActivity->id,$insertedNode->id);
                            }
                        }                        
                    }
                    else
                    {
                        throw new \RuntimeException("Function '{$function}' is not callable.");
                    }                    
                    break;
                case SwiftNodeDefinitionJoin::$P_XOR_SPLIT:
                case SwiftNodeDefinitionJoin::$P_XAND_SPLIT:
                    //Expected count more than 1
                    if(count($nextNodeDefinitionJoins) > 1)
                    {
                        /*
                         * Loop through node definitions
                         */
                        foreach($nextNodeDefinitionJoins as $n)
                        {
                            $function = $n->php_function."::".lcfirst(studly_case($n->name));
                            /*
                             * Call function if possible
                             */
                            if(is_callable($function))
                            {
                                /*
                                 * Check if we are allowed to create the next node
                                 */
                                if(call_user_func_array($function,array($currentNodeActivity,false)))
                                {
                                    $newNodeActivity = self::create($currentNodeActivity->workflow_activity_id,$n->childNode);
                                    if($newNodeActivity)
                                    {
                                        $insertedNodesArray[] = $newNodeActivity;
                                        self::join($currentNodeActivity->id,$newNodeActivity->id);
                                    }
                                }
                            }
                            else
                            {
                                throw new \RuntimeException("Function '{$function}' is not callable.");
                            }
                        }
                    }
                    else
                    {
                        throw new \RuntimeException("Node of 'XOR/XAND split' must have more than one branch");
                    }                    
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
     * Note: Never used, so never coded.
     * Should be one heck of a headache :3, similar to goNext().
     */
    public function goPrevious($nodeName)
    {
        
    }
    
    /*
     * Verify if user has access to this node
     * This is done by checking the flag column `permission_type` in the table swift_node_permission
     *
     * @param integer $node_definition_id
     *
     * @return boolean
     */
    public function hasAccess($node_definition_id,$permission_type=1)
    {
        //Super Man Access
        if(Sentry::findUserById($this->user_id)->isSuperUser())
        {
            return true;
        }
        
        $nodeDefinitionPermission = SwiftNodeDefinition::find($node_definition_id)->permission()->where('permission_type','=',$permission_type)->get()->all();
        if(count($nodeDefinitionPermission))
        {
            $arrayPermission = array_map(function(SwiftNodePermission $p){
                return $p->permission_name;
            },$nodeDefinitionPermission);
            return Sentry::findUserById($this->user_id)->hasAnyAccess((array)$arrayPermission);
        }
        
        return false;
    }
    
    /*
     * Helper Function: Permission - has Start node Access
     *
     * @param string $workflow_name
     *
     * @return boolean
     */
    public function hasStartAccess($workflow_name)
    {
        //Get Start node
        
        $nodeDefinition = NodeDefinition::getStartNodeDefinition($workflow_name);
        
        if($nodeDefinition)
        {
            return self::hasAccess($nodeDefinition->id);
        }
        else
        {
            return false;
        }
    }
    
    /*
     * Helper Function: Permission - has Kill node Access
     *
     * @param string $workflow_name
     *
     * @return boolean
     */
    public function hasKillAccess($workflow_name)
    {
        //Get Kill node
        
        $workflow = SwiftWorkflowType::getByName($workflow_name);
        
        if($workflow)
        {
            $nodeDefinition = SwiftNodeDefinition::getByType($workflow->id,SwiftNodeDefinition::$T_NODE_KILL)->first();

            return self::hasAccess($nodeDefinition->id);            
        }
        else
        {
            throw New \RuntimeException ("Workflow of name '{$workflow_name}' Not Found");
        }

    }
    
    /*
     * Helper Function: Permission - has End node Access
     *
     * @param string $workflow_name
     *
     * @return boolean
     */
    public function hasEndAccess($workflow_name)
    {
        //Get End Node
        
        $workflow = SwiftWorkflowType::getByName($workflow_name);
        
        if($workflow)
        {        
            $nodeDefinition = SwiftNodeDefinition::getByType($workflow->id,SwiftNodeDefinition::$T_NODE_END)->first();

            return self::hasAccess($nodeDefinition->id);
        }
        else
        {
            throw New \RuntimeException ("Workflow of name '{$workflow_name}' Not Found");
        }
    }
    
    /*
     * Helper Function: Fetches Start Node Activty
     *
     * @param string $workflow_name
     *
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function getStartNodeActivity($workflow_name)
    {
        $workflow = SwiftWorkflowType::getByName($workflow_name);
        
        if($workflow)
        {
            return SwiftNodeActivity::getByWorkflowAndDefinition($workflow->id,SwiftNodeDefinition::$T_NODE_START)->first();
        }
        else
        {
            throw New \RuntimeException ("Workflow of name '{$workflow_name}' Not Found");
        }        
    }
    
}