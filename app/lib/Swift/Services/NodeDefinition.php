<?php
/* 
 * Name: Node Activity
 */

Namespace Swift\Services;

Use \SwiftNodeDefinition;
Use \SwiftWorkflowType;

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
        
            return SwiftNodeDefinition::getByType($workflow->id,\SwiftNodeDefinition::$T_NODE_START)->first();

        }
        else
        {
            throw New \RuntimeException ("Workflow of name '{$workflow_name}' Not Found");
        }        
    }
    
    public function checkPermission(\SwiftNodeDefinition $nodeDefinition,$user)
    {
        $permissions = $nodeDefinition->load('permission');
        foreach($permissions as $p)
        {
            if($p->permission_type == \SwiftNodePermission::RESPONSIBLE && $user->hasAccess($p->permission_name))
            {
                return true;
            }
        }
        return false;
    }
}