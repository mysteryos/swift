<?php

NameSpace Swift\OrderTracking;

class NodeNotification {
    
    public static function sendNotification($workflow_activity_id,$permissions)
    {
        $workflowActivity = \SwiftWorkflowActivity::find($workflow_activity_id);
        if(count($workflowActivity))
        {
            $users = \Sentry::findAllUsersWithAnyAccess($permissions);
            if(count($users))
            {
                foreach($users as $u)
                {
                    if($u->activated && !$u->isSuperUser())
                    {
                        \Notification::send(\SwiftNotification::TYPE_RESPONSIBLE,$workflowActivity,$u);
                    }
                }   
            }
        }
    }
}