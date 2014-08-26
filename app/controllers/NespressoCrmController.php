<?php
/*
 * Name: Nespresso CRM Controller
 * Description:
 */

class NespressoCrmController extends UserController {
    
    public function __construct(){
        parent::__construct();
        $this->pageName = "Nespresso CRM";
    }      
    
    public function getMachines()
    {
        $this->pageTitle = 'Machines';
        
        return $this->makeView('nespresso-crm/machines');
    }
    
    public function getCreatemachine()
    {
        $this->pageTitle = 'Create machine entry';
        
        return $this->makeView('nespresso-crm/create-machine');
    }
}
    