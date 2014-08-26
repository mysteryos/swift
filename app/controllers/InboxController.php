<?php
/*
 * Name: Inbox Controller
 * Description:
 */

Class InboxController extends UserController {
    
    public function __construct(){
        parent::__construct();
        $this->pageName = "Inbox";
    }
    
    public function getIndex()
    {
        return $this->makeView('inbox-list');
    }
}