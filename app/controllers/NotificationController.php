<?php

class NotificationController extends UserController {
    
    public function getMarkread()
    {
        SwiftNotification::setRead($this->currentUser->id);
        return Response::json(array('result'=>1));
    }
}