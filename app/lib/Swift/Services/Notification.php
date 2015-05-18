<?php

Namespace Swift\Services;

class Notification {
    
    private $notification;
    private $icon;
    private $title;
    private $url;
    private $color;
    private $content;
    
    public function send($type,$object,$user=false)
    {
        $result = false;

        if(is_string($object))
        {
            $result = $this->generateStringComment($object,$user);
        }
        else
        {
            switch($type)
            {
                case \SwiftNotification::TYPE_INFO:
                    switch(get_class($object))
                    {
                        case "SwiftPR":
                            $result = $this->generatePRInvoiceCancelled($object);
                            break;
                    }
                    break;
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
        }
        
        if($result === true)
        {
            $this->push();
        }
    }
    
    public function push()
    {
        $this->notification->load('notifiable');
        if(\Config::get('pusher.enabled'))
        {
            $pusher = new \Pusher(\Config::get('pusher.app_key'), \Config::get('pusher.app_secret'), \Config::get('pusher.app_id'));
            $pusher->trigger('private-user-'.$this->notification->to,
                             'notification_new',
                             array('id'=>$this->notification->id,
                                   'color'=>$this->getColor(),
                                   'html'=>\View::make('notification/single',array('notification'=>$this->notification))->render(),
                                   'title'=>$this->getTitle(),
                                   'content'=>$this->getContent(),
                                   'url'=>$this->getUrl(),
                                   'icon'=>$this->getIcon()));
        }
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
        if(count($definition) && $definition->php_notification_function !== null)
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

    /*
     * SwiftPR: Invoice Cancelled Successfully
     */

    public function generatePRInvoiceCancelled($object)
    {
        $this->notification = new \SwiftNotification;
        $this->notification->msg = "Invoice has been cancelled";
        $this->notification->from = \Sentry::findUserByLogin(\Config::get('website.system_mail'))->id;
        $this->notification->to = $object->owner_user_id;
        $this->notification->type = \SwiftNotification::TYPE_INFO;
        $object->notification()->save($this->notification);
        return true;
    }

    public function generateStringComment($comment,$user_id)
    {
        $this->notification = new \SwiftNotification;
        $this->notification->msg = $comment;
        $this->notification->from = \Sentry::findUserByLogin(\Config::get('website.system_mail'))->id;
        $this->notification->to = $user_id;
        $this->notification->type = \SwiftNotification::TYPE_INFO;
        $this->notification->notifiable_type = "SwiftComment";
        $this->notification->notifiable_id = 0;
        $this->notification->save();
        $this->url = "#";
        $this->title = "New Comment";
        $this->icon = "fa-comment-o";
        return true;
    }

    private function getIcon()
    {
        if(!isset($this->icon))
        {
            switch($this->notification->type)
            {
                case \SwiftNotification::TYPE_STATISTICS:
                    $this->icon = "fa-bar-chart-o";
                    break;
                case \SwiftNotification::TYPE_RESPONSIBLE:
                    $this->icon = "fa-bell";
                    break;
                case \SwiftNotification::TYPE_ACTION:
                    $this->icon = "fa-bell";
                    break;
                case \SwiftNotification::TYPE_SUCCESS:
                    $this->icon = "fa-check";
                    break;
                case \SwiftNotification::TYPE_COMMENT:
                    $this->icon = "fa-comment-o";
                    break;
                case \SwiftNotification::TYPE_INFO:
                default:
                    $this->icon = "fa-cloud";
                    break;
            }
        }
    }

    private function getUrl()
    {
        if(!isset($this->url))
        {
            $this->url = \Helper::generateUrl($this->notification->notifiable);
        }
        return $this->url;
    }

    private function getTitle()
    {
        if(!isset($this->title))
        {
            $this->title = "<i class=\"fa {$this->notification->notifiable->getIcon()}\"></i> ".$this->notification->notifiable->getReadableName();
        }
        
        return $this->title;
    }

    private function getColor()
    {
        if(!isset($this->color))
        {
            switch($this->notification->type)
            {
                case \SwiftNotification::TYPE_INFO:
                    $this->color = "#3498db";
                    break;
                case \SwiftNotification::TYPE_STATISTICS:
                    $this->color = "#7f8c8d";
                    break;
                case \SwiftNotification::TYPE_SUCCESS:
                    $this->color = "#739E73";
                    break;
                case \SwiftNotification::TYPE_RESPONSIBLE:
                    $this->color = "#e67e22";
                    break;
                case \SwiftNotification::TYPE_COMMENT:
                    $this->color = "#7f8c8d";
                    break;
                case \SwiftNotification::TYPE_ACTION:
                default:
                    $this->color = "#3276b1";
                    break;
            }
        }

        return $this->color;
    }

    private function getContent()
    {
        if(!isset($this->content))
        {
            $this->content = $this->notification->msg;
        }

        return $this->content;
    }
}