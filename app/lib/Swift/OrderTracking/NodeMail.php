<?php
/*
 * Name:
 * Description:
 */

NameSpace Swift\OrderTracking;

class NodeMail {
    
    public static function sendMail($workflow_activity_id,$permissions)
    {
        $order = \SwiftWorkflowActivity::find($workflow_activity_id)->first()->workflowable()->first();
        $order->current_activity = \WorkflowActivity::progress($order,'order_tracking');
        $users = \Sentry::findAllUsersWithAnyAccess($permissions);
        if(count($users))
        {
            foreach($users as $u)
            {
                //\Log::info(\View::make('emails.order-tracking.pending',array('order'=>$order,'user'=>$u))->render());
                \Mail::send('emails.order-tracking.pending',array('order'=>$order,'user'=>$u),function($message) use ($u){
                    $message->subject(\Config::get('website.name'));
                    $message->to($u->email);
                });             
            }   
        }
    }
}