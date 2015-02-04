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
        /*
         * Todo List
         */
        
        $userMergedPermissions = (array)array_keys($this->currentUser->getMergedPermissions());
        
        $context = Config::get('context');
        $this->data['todoList'] = new \Illuminate\Support\Collection;
        
        foreach($context as $k=>$v)
        {
            if(is_callable($v."::getInProgressResponsible"))
            {
                $contextClass = new $v;
                //Get Responsible workflows - important and not important data
                $result = $contextClass::getInProgressResponsible()
                            ->merge($contextClass::getInProgressResponsible(0,true));
                $this->data['todoList']->merge($result);
            }
        }
        
        return $this->makeView('dashboard');
    }
    
    public function getStories($startfrom=0)
    {
        $context = Config::get('context');
        $contextArray = array();
        
        foreach($context as $k=>$v)
        {
            if($this->currentUser->hasAccess(\Config::get('permission.'.$k.'.view')))
            {
                $contextArray[] = $v;
            }            
        }
        
        if(!empty($contextArray))
        {
            $this->data['stories'] = Story::fetch($contextArray,30,0);
        }
        else
        {
            $this->data['stories'] = [];
        }
        
        $this->data['dynamicStory'] = false;
        
        echo View::make('story/chapter',$this->data)->render();
    }
    
    public function getLatenodes()
    {
        $lateNodes = array();
        $context = Config::get('context');
        
        foreach($context as $k=>$v)
        {
            if($this->currentUser->hasAnyAccess([\Config::get('permission.'.$k.'.view'),\Config::get('permission.'.$k.'.admin')]))
            {
                $lateNodes[$k] = array(
                    'late_count' => SwiftNodeActivity::countLateNodes($k),
                    'late_total_count' => SwiftNodeActivity::countPendingNodesWithEta($k),
                    'name'=> (new $v)->readableName,
                    'icon'=> (new $v)->getIcon()
                );
            }
        }
        
        $this->data['lateNodes'] = $lateNodes;
        
        echo View::make('dashboard/systemhealth',$this->data)->render();
    }
}