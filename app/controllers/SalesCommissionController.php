<?php

class SalesCommissionController extends UserController {
    public function __construct(){
        parent::__construct();
        $this->pageName = "Sales Comission";
        $this->rootURL = $this->context = "sales-commission";
        $this->adminPermission = \Config::get("permission.{$this->context}.admin");
        $this->viewPermission = \Config::get("permission.{$this->context}.view");
    }
    
    public function getIndex()
    {
        return Redirect::to('/'.$this->context.'/overview');
    }
    
    public function getOverview()
    {
        
    }
}