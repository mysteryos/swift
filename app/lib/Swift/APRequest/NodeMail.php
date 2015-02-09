<?php
/*
 * Name:
 * Description:
 */

NameSpace Swift\APRequest;

class NodeMail {
    
    public static function sendMail($workflowActivity,$permissions)
    {
        if(count($workflowActivity))
        {
            $users = \Sentry::findAllUsersWithAnyAccess($permissions);
            if(count($users))
            {
                $aprequest = $workflowActivity->workflowable;
                $aprequest->current_activity = \WorkflowActivity::progress($workflowActivity);                
                $mailData = [
                        'name'=>$aprequest->name,
                        'id'=>$aprequest->id,
                        'current_activity'=>$aprequest->current_activity,
                        'url'=>\Helper::generateUrl($aprequest,true),
                        ];
                
                foreach($users as $u)
                {
                    if($u->activated && !$u->isSuperUser())
                    {
                        try
                        {
                            //\Log::info(\View::make('emails.order-tracking.pending',array('order'=>$aprequest,'user'=>$u))->render());
                            \Mail::queue('emails.aprequest.pending',array('aprequest'=>$mailData,'user'=>$u),function($message) use ($u,$aprequest){
                                $message->from('swift@scott.mu','Scott Swift');
                                $message->subject(\Config::get('website.name').' - Status update on A&P Request "'.$aprequest->name.'" ID: '.$aprequest->id);
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
    
    public static function sendCancelledMail($aprequest)
    {
        //Get owner of AP Request
        $owner_user = \Sentry::find($aprequest->requester_user_id);
        
        if($owner_user->activated)
        {
            \Mail::queue('emails.aprequest.pending',array('aprequest'=>$aprequest,'user'=>$owner_user),function($message) use ($owner_user,$aprequest){
                $message->from('swift@scott.mu','Scott Swift');
                $message->subject(\Config::get('website.name').' - A&P Request Cancelled"'.$aprequest->name.'" ID: '.$aprequest->id);
                $message->to($owner_user->email);
            });            
        }
    }
}