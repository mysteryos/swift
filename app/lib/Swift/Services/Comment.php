<?php

Namespace Swift\Services;

Use Sentry;
Use SwiftComment;
Use Eloquent;
Use Eloquent\Model;
Use Crypt;

/**
 * Description of Comment
 *
 * @author kpudaruth
 */
class Comment {
    
    
    public function makeKey($commentable)
    {
        return \get_class($commentable) . '.' . $commentable->getKey();
    }
    
    public function getKey($commentable)
    {
        if (empty($commentable))
        {
                throw new Exception();
        }
        $commentable = Crypt::decrypt($commentable);
        if (strpos($commentable, '.') == false)
        {
                throw new Exception();
        }
        $explodedArray = explode('.', $commentable);
        list($commentableType, $commentableId) = $explodedArray;
        if (!\class_exists("\\".$commentableType))
        {
                throw new Exception();
        }
        
        return $explodedArray;
    }
    
    public function mailNotify($comment, $user_array)
    {
        $current_user = Sentry::getUser();
        $classObj = new $comment->commentable_type;
        $classObj = $classObj::find($comment->commentable_id);
        
        if(count($classObj))
        {
            foreach($user_array as $u)
            {
                $user = Sentry::findUserById($u);
                if(count($user))
                {
//                    \Log::info(\View::make('emails.comment.notify',array('obj'=>$classObj,'user'=>$user ,
//                                                                'comment_user'=>$current_user,
//                                                                'comment' => $comment))->render());
                    \Mail::send('emails.comment.notify',array('obj'=>$classObj,'user'=>$user ,
                                                                'comment_user'=>$current_user,
                                                                'comment' => $comment),function($message) use ($user){
                        $message->subject(\Config::get('website.name')." - You've been mentionned in a comment");
                        $message->to($user->email);
                    }); 
                }
            }
        }
    }

}
