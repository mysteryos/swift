<?php
/*
 * Name:
 * Description:
 */

NameSpace Swift\OrderTracking;

class NodeMail {
    
    private static function sendmail($workflow_activity_id,$permissions)
    {
        $order = \SwiftWorkflowActivity::find($workflow_activity_id)->first()->workflowable()->first();
        $order->current_activity = \WorkflowActivity::progress($order,'order_tracking');
        $users = \Sentry::findAllUsersWithAnyAccess(array('ot-preparation'));
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
    
    /*
     * Name: otPreparation
     * Description: Flags preparation as complete when bill of lading is received, aka, order has been shipped
     */
    public static function otPreparation($workflow_activity_id)
    {
        return self::sendMail($workflow_activity_id,array('ot-preparation'));
    }
    
    /*
     * Name: otTransit
     * Description: Flags transit as complete when notice of arrival has been uploaded.
     */
    public static function otTransit($workflow_activity_id)
    {
        return self::sendMail($workflow_activity_id,array('ot-preparation'));         
    }
    
    /*
     * Name: otCustoms
     * Description: Flags customs clearance as completw when bill of entry has been uploaded and
     * customs information has been properly filled in.
     */
    public static function otCustoms($workflow_activity_id)
    {
        return self::sendMail($workflow_activity_id,array('ot-customs'));         
    }
    
    /*
     * Name: otPickup
     * Description: Flags pickup as complete when preparation for pickup is complete
     */
    public static function otPickup($workflow_activity_id)
    {
        return self::sendMail($workflow_activity_id,array('ot-pickup'));       
    }
    
    public static function otReception($workflow_activity_id)
    {
        return self::sendMail($workflow_activity_id,array('ot-reception'));
    }
    
    public static function otCosting($workflow_activity_id)
    {
        return self::sendMail($workflow_activity_id,array('ot-costing'));
    } 
    
    public static function otEnd($workflow_activity_id)
    {
        return self::sendMail($workflow_activity_id,array('ot-admin'));
    }
}