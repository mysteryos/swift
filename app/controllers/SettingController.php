<?php

class SettingController extends UserController {
    public function __construct(){
        parent::__construct();
        $this->pageName = "Settings";
    }

    public function getOverview()
    {
        $this->pageTitle = 'Overview';
    }

    public function postSaveSetting()
    {

    }
    
}