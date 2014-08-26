<?php
/* 
 * Name: Node Activity
 */

Namespace Swift\Services;

Use \SwiftNodeActivity;
Use \SwiftNodePermission;
Use \SwiftNodeDefinition;
Use \SwiftWorkflowActivity;
Use \SwiftWorkflowType;
use \Sentry;

class NodeDefinition {
    /*
     * Helper Function: Get Start Node Definition
     */
    
    public function getStartNodeDefinition($workflow_name)
    {
        //Get Start node
        
        $workflow = SwiftWorkflowType::getByName($workflow_name);
        
        if(count($workflow))
        {
        
            return SwiftNodeDefinition::getByType($workflow->id,SwiftNodeDefinition::$T_NODE_START)->first();

        }
        else
        {
            throw New \RuntimeException ("Workflow of name '{$workflow_name}' Not Found");
            return false;
        }        
    }    
}