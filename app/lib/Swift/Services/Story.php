<?php

Namespace Swift\Services;

class Story {
    
    private $story;
    
    //I.e Save function
    public function relate($obj,$action,$type=1,$context_type=false,$context_id=0)
    {
        $this->story = new \SwiftStory;
        if($context_type===false)
        {
            switch(get_class($obj))
            {
                case "SwiftComment":
                    $this->story->context_type = $obj->commentable_type;
                    $this->story->context_id = $obj->commentable_id;
                    break;
                case "SwiftWorkflowActivity":
                    $this->story->context_type = $obj->workflowable_type;
                    $this->story->context_id = $obj->workflowable_id;
                    break;
                case "SwiftNodeActivity":
                    $this->story->context_type = $obj->workflowactivity->workflowable_type;
                    $this->story->context_id = $obj->workflowactivity->workflowable_id;
                    break;
                default:
                    $this->story->context_type = \get_class($obj);
                    $this->story->context_id = $obj->id;
                    break;
            }
        }
        else
        {
            $this->story->context_type = $context_type;
            $this->story->context_id = $context_id;
        }
        
        $this->story->action = $action;
        
        $obj->story()->save($this->story);
        $this->push();
    }
    
    public function relateTask($job,$data)
    {
        $obj = new $data['obj_class'];
        $obj = $obj::find($data['obj_id']);
        
        if(isset($data['user_id']))
        {
            $user = \Sentry::findUserById($data['user_id']);
            if(count($user))
            {
                \Sentry::login($user,false);
            }
        }
        else
        {
            \Helper::loginSysUser();
        }
        
        if(\Sentry::check() && count($obj))
        {
            $data['context'] = isset($data['context']) ? $data['context'] : false;
            $data['type'] = isset($data['type']) ? $data['type'] : \SwiftStory::TYPE_STATIC;
            $this->relate($obj,$data['action'],$data['type'],$data['context']);
        }
        
        $job->delete();
        
    }
    
    public function push()
    {
        $context = array_search($this->story->context_type,\Config::get('context'));
        if($context !== false)
        {
            $users = \Sentry::findAllUsersWithAnyAccess((array)\Config::get('permission.'.$context.'.view'));
            if(count($users))
            {
                $storyHtml = \View::make('story/single',array('story'=>$this->story))->render();
                if(\Config::get('pusher.enabled'))
                {
                    $pusher = new \Pusher(\Config::get('pusher.app_key'), \Config::get('pusher.app_secret'), \Config::get('pusher.app_id'));
                    foreach($users as $u)
                    {
                        $pusher->trigger('private-user-story-'.$u->id,
                                         'story_new',
                                         array('id'=>$this->story->id,
                                               'html'=>$storyHtml));                 
                    }
                }
            }
        }
    }
    
    public function fetch($context=false,$take=10,$offsetId=0,$filters=array())
    {
        $contextArray = array();
        
        if($context===false)
        {
            $contextArray = $this->context();
        }
        else
        {
            if(is_array($context))
            {
                $contextArray = $context;
            }
            else
            {
                $contextArray[] = $context;
            }
        }
        
        if(!empty($contextArray))
        {
            $stories = \SwiftStory::orderBy('created_at','DESC')->whereIn('context_type',$contextArray)->take($take)->with(array('storyfiable','byUser'));
            if($offsetId>0)
            {
                $stories->where('id','<',$offsetId);
            }
            
            if(!empty($filters))
            {
                $stories->with(['context'=>function ($q) use ($filters){
                    foreach($filters as $f)
                    {
                        $q->where($f[0],$f[1],$f[2]);
                    }
                    return $q;
                }]);
            }
            
            $stories = $stories->get();
            //increment View count
            if(count($stories))
            {
                \Queue::push('Story@viewIncrementTask',array('stories'=>$stories));
            }
            return $stories;
        }
        else
        {
            return array();
        }
    }
    
    public function total($context=false)
    {
        
    }
    
    public function viewIncrementTask($job,$data)
    {
        foreach($data['stories'] as $s)
        {
            $story = \SwiftStory::find($s['id']);
            if(count($story))
            {
                $story->increment('view');
            }
        }
        $job->delete();
    }
    
    /*
     * Returns: Array
     */
    private function context()
    {
        $contextArray = array();
        
        $context = \Config::get('context');
        //SuperUsers see all stuffs
        if(\Sentry::getUser()->isSuperuser())
        {
            array_walk(\Config::get('context'),function($v,$k){
                $contextArray[] = $v;
            });
        }
        else
        {
            $userPermissions = (array)array_keys(\Sentry::getUser()->getMergedPermissions());

            foreach(\Config::get('context') as $k=>$v)
            {
                if(in_array(\Config::get('permission.'.$k.'.view'),$userPermissions))
                {
                    $contextArray[] = $v;
                }            
            }
        }
        
        return $contextArray;
    }
    
}