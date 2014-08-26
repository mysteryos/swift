<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

Class AdminController extends UserController {
    
    public function __construct(){
        parent::__construct();
        $this->pageName = "Admin";
    }    
    
    public function getIndex()
    {
        
    }
    
    public function getUsers()
    {
        $this->data['pageTitle'] = "Users";
        $this->data['users'] = User::all();
        
        return $this->makeView('admin.users');
    }
    
    
}