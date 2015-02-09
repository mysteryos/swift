<?php

NameSpace Swift\OrderTracking;

class NodeMail {
    
    public static function sendMail($workflowActivity,$permissions)
    {
        if(count($workflowActivity))
        {
            $users = \Sentry::findAllUsersWithAnyAccess($permissions);
            if(count($users))
            {
                $order = $workflowActivity->workflowable;
                $order->current_activity = \WorkflowActivity::progress($workflowActivity);                
                $mailData = [
                        'name'=>$order->name,
                        'id'=>$order->id,
                        'current_activity'=>$order->current_activity,
                        'url'=>\Helper::generateUrl($order,true),
                        ];
                
                foreach($users as $u)
                {
                    if($u->activated && !$u->isSuperUser())
                    {
                        try
                        {
                            //\Log::info(\View::make('emails.order-tracking.pending',array('order'=>$order,'user'=>$u))->render());
                            \Mail::queue('emails.order-tracking.pending',array('order'=>$mailData,'user'=>$u),function($message) use ($u,$order){
                                $message->from('swift@scott.mu','Scott Swift');
                                $message->subject(\Config::get('website.name').' - Status update on Order Process "'.$order->name.'" ID: '.$order->id);
                                $message->to($u->email);
                            });
                        }
                        catch (Exception $e)
                        {
                            \Log::error(get_class().': Mail sending failed with message: '.$e->getMessage().'\n Variable Dump: '.var_dump(get_defined_vars()));
                        }
                    }
                }   
            }
        }
    }
}