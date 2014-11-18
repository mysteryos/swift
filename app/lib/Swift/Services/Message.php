<?php
/* 
 * Name: Messages
 */

Namespace Swift\Services;

class Message {
    
    /*
     * Creates a new message
     */
    public function create($morph,$content,$subject,$user_id)
    {
        
    }
    
    public function send($message,$users)
    {
        
    }
    
    public function inbox($morph_name,$user_id=0)
    {
        if($user_id = 0)
        {
            $user_id = Sentry::getUser()->id;
        }
        //$messages = SwiftMessageUser::with('message')->where('user_id','=',);
    }
    
}