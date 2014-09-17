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
                \Mail::send('emails.order-tracking.pending',array('order'=>$order,'user'=>$u),function($message) use ($u,$order){
                    $message->from('no-reply@scottltd.net','Scott Swift');
                    $message->subject(\Config::get('website.name').' - Status update on Order Process "'.$order->name.'" ID: '.$order->id);
                    $message->to($u->email);
                });             
            }   
        }
    }
}