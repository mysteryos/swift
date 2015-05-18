<?php
namespace Swift\PR;

/**
 * Description of NodeMail
 *
 * @author kpudaruth
 */
class NodeMail
{
    //put your code here

    public static function sendCancelledMail($pr)
    {
        //Get owner of AP Request
        $owner_user = \Sentry::find($pr->owner_user_id);

        if($owner_user->activated)
        {
            \Mail::queueOn('sqs-mail','emails.aprequest.pending',array('pr'=>$pr,'user'=>$owner_user),function($message) use ($owner_user,$pr){
                $message->from('swift@scott.mu','Scott Swift');
                $message->subject(\Config::get('website.name').' - Product Returns Cancelled - "'.$pr->customer_name.'" (ID: '.$pr->id).")";
                $message->to($owner_user->email);
            });
        }
    }
}