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
        //Variable Declarations
        
        $this->data['todoList'] =
        $this->data['latestWorkflows'] = false;
        /*
         * Todo List
         */
        
        if(!$this->currentUser->isSuperUser())
        {
            $this->generateTodoList();
        }
        
        /*
         * Is an Admin Somewhere?
         */
        
        $userPermissions = array_keys($this->currentUser->getMergedPermissions());
        
        $adminPermission = array_filter($userPermissions,function($v){
            return (strpos($v,'-admin') !== false);
        });
        
        $this->data['isAdmin'] = !empty($adminPermission) || $this->currentUser->isSuperUser();
        
        
        /*
         * Display Latest Workflows if Admin
         */
        
        if($this->data['isAdmin'])
        {
            $this->generatelatestworkflow();
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
            $this->data['stories'] = Story::fetch($contextArray,20,0);
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
    
    private function generateTodoList()
    {
        $perPage = 15;
        $context = Config::get('context');
        $this->data['todoList'] = SwiftWorkflowActivity::getInProgressResponsible($context,$perPage);

        foreach($this->data['todoList'] as &$row)
        {
            $row->current_activity = WorkflowActivity::progress($row);
            $row->activity = Helper::getMergedRevision($row->workflowable->revisionRelations,$row->workflowable);                    
        }        
    }    
    
    private function generatelatestworkflow()
    {
        $perPage = 15;

        if($this->currentUser->isSuperUser())
        {
            $this->data['latestWorkflows'] = SwiftWorkflowActivity::getInProgress(array(),$perPage);
            foreach($this->data['latestWorkflows'] as &$row)
            {
                $row->current_activity = WorkflowActivity::progress($row);
                $row->activity = Helper::getMergedRevision($row->workflowable->revisionRelations,$row->workflowable);                    
            }            
        }
        else
        {
            $userPermission = array_keys($this->currentUser->getMergedPermissions());

            $adminPermission = array_filter($userPermission,function($v){
                return (strpos($v,'-admin') !== false);
            });

            if(!empty($adminPermission))
            {
                $permission = Config::get('permission');
                $context = array();
                foreach($permission as $k=>$p)
                {
                    if(count(array_intersect($p, $adminPermission))>0)
                    {
                        $context[] = $k;
                    }
                }

                if(!empty($context))
                {
                    $contextClass = array_intersect_key(\Config::get('context'), array_flip($context));

                    if(!empty($contextClass))
                    {
                        $this->data['latestWorkflows'] = SwiftWorkflowActivity::getInProgress($contextClass,$perPage);
                        foreach($this->data['latestWorkflows'] as &$row)
                        {
                            $row->current_activity = WorkflowActivity::progress($row);
                            $row->activity = Helper::getMergedRevision($row->workflowable->revisionRelations,$row->workflowable);                    
                        }                    
                    }
                }
            }
        }        
    }
    
    /*
     * Infinite Scroll GET call
     */    
    public function getLatestworkflow()
    {
        $this->data['latestWorkflows'] = false;
        $this->generatelatestworkflow();
        echo View::make('dashboard/latestworkflow',$this->data)->render();
    }
    
    /*
     * Infinite Scroll GET call
     */
    public function getTodolist()
    {
        $this->data['todoList'] = false;
        $this->generateTodoList();
        echo View::make('dashboard/todolist',$this->data)->render();
    }
}