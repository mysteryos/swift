<?php

Namespace Swift\Services;

class Notification {
    
    private $notification;
    
    public function send($type,$object,$user)
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
                switch(get_class())
                {
                    case "SwiftNodeActivity":
                        $result = $this->generateNodeSuccess($object,$user);
                        break;
                }
                break;
        }
        
        if($result === true)
        {
            $this->push($this->notification);
        }
    }
    
    public function push($notification)
    {
        switch($notification->type)
        {
            case \SwiftNotification::TYPE_INFO:
            case \SwiftNotification::TYPE_STATISTICS:
                $color = "#739E73";
                break;
            case \SwiftNotification::TYPE_RESPONSIBLE:
            case \SwiftNotification::TYPE_ACTION:
            default:
                $color = "#3276b1";
                break;            
        }
        
        switch($notification->type)
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
            case \SwiftNotification::TYPE_INFO:
            default:
                $icon = "fa-cloud";
                break;            
        }
        
        $notification->load('notifiable');
        $pusher = new \Pusher(\Config::get('pusher.app_key'), \Config::get('pusher.app_secret'), \Config::get('pusher.app_id'));
        $pusher->trigger('private-user-'.$notification->to,
                         'notification-new',
                         array('id'=>$notification->id,
                               'color'=>$color,
                               'html'=>\View::make('notification/single',array('notification'=>$notification)),
                               'title'=>$notification->notifiable->getReadableName(),
                               'content'=>$notification->msg,
                               'url'=>Helper::generateUrl($notification->notifiable),
                               'icon'=>$icon));
    }
    
    /*
     * SwiftWorkflowActivity: Notification to responsible user for pending nodes
     */
    public function generateWorkflowResponsibility($object,$user) {
        $relation_object = $object->workflowable;
        if(count($relation_object))
        {
            $notif = new \SwiftNotification;
            $notif->msg = 'awaits your input.';
            $notif->from = \Sentry::getUser()->id;
            $notif->to = $user->id;
            $notif->type = \SwiftNotification::TYPE_RESPONSIBLE;
            $relation_object->notification()->save($notif);
            $this->notification = $notif;
            return true;
        }
        
        return false;
    }
    
    /*
     * SwiftNodeActivity: On Success TBD
     */
    
    public function generateNodeSuccess($object,$user)
    {
        $workflow = $object->workflowactivity;
        
    }
}