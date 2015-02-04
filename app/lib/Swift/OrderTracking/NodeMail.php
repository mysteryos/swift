<?php

NameSpace Swift\OrderTracking;

class NodeMail {
    
    public static function sendMail($workflow_activity_id,$permissions)
    {
        $workflowActivity = \SwiftWorkflowActivity::find($workflow_activity_id);
        if(count($workflowActivity))
        {
            $order = $workflowActivity->workflowable;
            $order->current_activity = \WorkflowActivity::progress($order);
            $users = \Sentry::findAllUsersWithAnyAccess($permissions);
            if(count($users))
            {
                foreach($users as $u)
                {
                    if($u->activated && !$u->isSuperUser())
                    {
                        //\Log::info(\View::make('emails.order-tracking.pending',array('order'=>$order,'user'=>$u))->render());
                        \Mail::queue('emails.order-tracking.pending',array('order'=>$order,'user'=>$u),function($message) use ($u,$order){
                            $message->from('swift@scott.mu','Scott Swift');
                            $message->subject(\Config::get('website.name').' - Status update on Order Process "'.$order->name.'" ID: '.$order->id);
                            $message->to($u->email);
                        });
                    }
                }   
            }
        }
    }
}