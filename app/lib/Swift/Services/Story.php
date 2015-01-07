<?php

Namespace Swift\Services;

class Story {
    
    private $story;
    
    //I.e Save function
    public function relate($obj,$action,$type=1,$context=false)
    {
        $this->story = new \SwiftStory;
        if($context===false)
        {
            switch(get_class($obj))
            {
                case "SwiftComment":
                    $this->story->context = $obj->commentable_type;
                    break;
                case "SwiftWorkflowActivity":
                    $this->story->context = $obj->workflowable_type;
                    break;
                case "SwiftNodeActivity":
                    $this->story->context = $obj->workflowactivity->workflowable_type;
                    break;
                default:
                    $this->story->context = \get_class($obj);
                    break;
            }
        }
        else
        {
            $this->story->context = $context;
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
        $context = array_search($this->story->context,\Config::get('context'));
        if($context !== false)
        {
            $users = \Sentry::findAllUsersWithAnyAccess((array)\Config::get('permission.'.$context.'.view'));
            if(count($users))
            {
                $storyHtml = \View::make('story/single',array('story'=>$this->story))->render();
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
    
    public function fetch($context=false,$take=10,$offsetId=0)
    {
        $contextArray = array();
        
        if($context===false)
        {
            $context = Config::get('context');
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
        }
        else
        {
            $contextArray[] = $context;
        }
        
        $stories = \SwiftStory::orderBy('created_at','DESC')->whereIn('context',$contextArray)->take($take)->with('storyfiable','byUser');
        if($offsetId>0)
        {
            $stories->where('id','<',$offsetId);
        }
        
        $stories = $stories->get();
        //increment View count
        if(count($stories))
        {
            \Queue::push('Story@viewIncrementTask',array('stories'=>$stories));
        }
        return $stories;
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
    
}