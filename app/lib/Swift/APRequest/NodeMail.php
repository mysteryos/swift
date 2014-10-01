<?php
/*
 * Name:
 * Description:
 */

NameSpace Swift\APRequest;

class NodeMail {
    
    public static function sendMail($workflow_activity_id,$permissions)
    {
        $aprequest = \SwiftWorkflowActivity::find($workflow_activity_id)->first()->workflowable()->first();
        $aprequest->current_activity = \WorkflowActivity::progress($aprequest,'order_tracking');
        $users = \Sentry::findAllUsersWithAnyAccess($permissions);
        if(count($users))
        {
            foreach($users as $u)
            {
                if($u->activated && $u->isSuperUser())
                {
                    //\Log::info(\View::make('emails.order-tracking.pending',array('order'=>$aprequest,'user'=>$u))->render());
                    \Mail::send('emails.aprequest.pending',array('aprequest'=>$aprequest,'user'=>$u),function($message) use ($u,$aprequest){
                        $message->from('no-reply@scottltd.net','Scott Swift');
                        $message->subject(\Config::get('website.name').' - Status update on A&P Request "'.$aprequest->name.'" ID: '.$aprequest->id);
                        $message->to($u->email);
                    });     
                }
            }   
        }
    }
}