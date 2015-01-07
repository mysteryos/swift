<?php

Namespace Swift\Services;

class Notification {
    
    private $notification;
    
    public function send($type,$object,$user=false)
    {
        $result = false;
        
        switch($type)
        {
            case \SwiftNotification::TYPE_RESPONSIBLE:
                switch(get_class($object))
                {
                    case "SwiftWorkflowActivity":
                        $result = $this->generateWorkflowResponsibility($object,$user);
                        break;
                }
                break;
            case \SwiftNotification::TYPE_SUCCESS:
                switch(get_class($object))
                {
                    case "SwiftNodeActivity":
                        $result = $this->generateNodeSuccess($object);
                        break;
                }
                break;
            case \SwiftNotification::TYPE_COMMENT:
                switch(get_class($object))
                {
                    case "SwiftComment":
                        $result = $this->generateCommentMention($object,$user);
                        break;
                }
                break;
        }
        
        if($result === true)
        {
            $this->push();
        }
    }
    
    public function push()
    {
        switch($this->notification->type)
        {
            case \SwiftNotification::TYPE_INFO:
                $color = "#3498db";
                break;
            case \SwiftNotification::TYPE_STATISTICS:
                $color = "#7f8c8d";
                break;
            case \SwiftNotification::TYPE_SUCCESS:
                $color = "#739E73";
                break;
            case \SwiftNotification::TYPE_RESPONSIBLE:
                $color = "#e67e22";
                break;
            case \SwiftNotification::TYPE_COMMENT:
                $color = "#7f8c8d";
                break;
            case \SwiftNotification::TYPE_ACTION:
            default:
                $color = "#3276b1";
                break;
        }
        
        switch($this->notification->type)
        {
            case \SwiftNotification::TYPE_STATISTICS:
                $icon = "fa-bar-chart-o";
                break;
            case \SwiftNotification::TYPE_RESPONSIBLE:
                $icon = "fa-bell";
                break;
            case \SwiftNotification::TYPE_ACTION:            
                $icon = "fa-bell";
                break;
            case \SwiftNotification::TYPE_SUCCESS:
                $icon = "fa-check";
                break;
            case \SwiftNotification::TYPE_COMMENT:
                $icon = "fa-comment-o";
                break;
            case \SwiftNotification::TYPE_INFO:
            default:
                $icon = "fa-cloud";
                break;
        }
        
        $this->notification->load('notifiable');
        $pusher = new \Pusher(\Config::get('pusher.app_key'), \Config::get('pusher.app_secret'), \Config::get('pusher.app_id'));
        $pusher->trigger('private-user-'.$this->notification->to,
                         'notification_new',
                         array('id'=>$this->notification->id,
                               'color'=>$color,
                               'html'=>\View::make('notification/single',array('notification'=>$this->notification))->render(),
                               'title'=>"<i class=\"fa {$this->notification->notifiable->getIcon()}\"></i> ".$this->notification->notifiable->getReadableName(),
                               'content'=>$this->notification->msg,
                               'url'=>\Helper::generateUrl($this->notification->notifiable),
                               'icon'=>$icon));
    }
    
    /*
     * SwiftWorkflowActivity: Notification to responsible user for pending nodes
     */
    public function generateWorkflowResponsibility($object,$user) {
        $relation_object = $object->workflowable;
        if(count($relation_object))
        {
            $this->notification = new \SwiftNotification;
            $this->notification->msg = 'awaits your input.';
            $this->notification->from = \Sentry::getUser()->id;
            $this->notification->to = $user->id;
            $this->notification->type = \SwiftNotification::TYPE_RESPONSIBLE;
            $relation_object->notification()->save($this->notification);
            return true;
        }
        
        return false;
    }
    
    /*
     * SwiftNodeActivity: On Success TBD
     */
    
    public function generateNodeSuccess($object)
    {
        $definition = $object->definition;
        $workflow = $object->workflowactivity;
        if(count($definition))
        {
            $this->notification = new \SwiftNotification;
            $this->notification->msg = 'You completed step <b>'.$definition->label.'</b>';
            $this->notification->from = \Sentry::findUserByLogin(\Config::get('website.system_mail'))->id;
            $this->notification->to = \Sentry::getUser()->id;
            $this->notification->type = \SwiftNotification::TYPE_SUCCESS;
            $workflow->workflowable->notification()->save($this->notification);
            return true;
        }
        return false;
    }
    
    /*
     * SwiftComment: User Mentionned in Comment
     */
    public function generateCommentMention($comment,$user)
    {
        $this->notification = new \SwiftNotification;
        $this->notification->msg = "You have been mentionned in a comment: '{$comment->comment}'";
        $this->notification->from = $comment->user_id;
        $this->notification->to = $user->id;
        $this->notification->type = \SwiftNotification::TYPE_COMMENT;
        $comment->commentable->notification()->save($this->notification);
        return true;
    }
}