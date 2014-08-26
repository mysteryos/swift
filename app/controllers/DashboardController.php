<?php

/* 
 * Name: Dashboard Controller
 */

Class DashboardController extends UserController {
    
    public function __construct(){
        parent::__construct();
        $this->pageName = "Dashboard";
    }    
    
    public function getIndex()
    {
        return $this->makeView('dashboard');
    }
}