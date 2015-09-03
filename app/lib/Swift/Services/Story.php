<?php

/*
 * Name: Story
 * Description: Generates stories for timelines of each module on their overview page.
 */

Namespace Swift\Services;

class Story {
    
    private $story;

    public $user_id;

    public function __construct()
    {
        $this->user_id = \Helper::getUserId();
    }

    /*
     * Save Story
     *
     * @param \Illuminate\Database\Eloquent\Model $obj
     * @param integer $action
     * @param integer $type
     * @param boolean|string $context_type
     * @param integer $context_id
     *
     */
    public function relate($obj,$action,$type=1,$context_type=false,$context_id=0)
    {
        $this->story = new \SwiftStory([
            'by' => $this->user_id
        ]);
        
        if($context_type===false)
        {
            switch(get_class($obj))
            {
                case "SwiftComment":
                    $this->story->context_type = $obj->commentable_type;
                    $this->story->context_id = $obj->commentable_id;
                    $this->story->by = $obj->user_id;
                    break;
                case "SwiftWorkflowActivity":
                    $this->story->context_type = $obj->workflowable_type;
                    $this->story->context_id = $obj->workflowable_id;
                    break;
                case "SwiftNodeActivity":
                    $this->story->context_type = $obj->workflowactivity->workflowable_type;
                    $this->story->context_id = $obj->workflowactivity->workflowable_id;
                    $this->story->by = $obj->user_id;
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

        //Check if story already exists
        if(!$this->checkExisting($obj))
        {
            $obj->story()->save($this->story);
            $this->push();
        }
    }

    /*
     * Check if story already exists
     *
     * @param \Illuminate\Database\Eloquent\Model $obj
     *
     * @return boolean
     */
    private function checkExisting(\Illuminate\Database\Eloquent\Model $obj)
    {
        return (boolean)\SwiftStory::where('context_type','=',$this->story->context_type)
                ->where('context_id','=',$this->story->context_id,'AND')
                ->where('storyfiable_type','=',get_class($obj),'AND')
                ->where('storyfiable_id','=',$obj->getKey(),'AND')
                ->count();
    }

    /*
     * Laravel Queue: Relate Task
     *
     * Retrieves information to generate a story
     *
     * @param mixed $job
     * @param array $data
     */
    public function relateTask($job,$data)
    {
        $obj = new $data['obj_class'];
        $obj = $obj::find($data['obj_id']);
        
        if(isset($data['user_id']))
        {
           $this->user_id = $data['user_id'];
        }
        
        if(count($obj))
        {
            $data['context'] = isset($data['context']) ? $data['context'] : false;
            $data['type'] = isset($data['type']) ? $data['type'] : \SwiftStory::TYPE_STATIC;
            $this->relate($obj,$data['action'],$data['type'],$data['context']);
        }
        
        $job->delete();
        
    }

    /*
     * Send notification popup through Pusher (https://pusher.com/)
     */
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
                                               'context'=>$context,
                                               'html'=>$storyHtml));                 
                    }
                }
            }
        }
    }

    /*
     * Get list of stories based on context
     *
     * @param boolean|array $context
     * @param integer $take
     * @param integer $offsetId
     * @param array $filters
     *
     * @return \Illuminate\Support\Collection|array
     */
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
            $stories = \SwiftStory::orderBy('created_at','DESC')
                        ->whereIn('context_type',$contextArray)
                        ->take($take)
                        ->with(array('storyfiable','byUser'));
            
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
                foreach($stories as $k=>$s)
                {
                    if(!$s->storyfiable)
                    {
                        unset($stories[$k]);
                    }
                }
                \Queue::push('Story@viewIncrementTask',array('stories'=>$stories));
            }
            return $stories;
        }
        else
        {
            return array();
        }
    }

    /*
     * Increments view count for each story
     *
     * @param mixed $job
     * @param array $data
     */
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
     * Generates a list of context that the user has access to.
     * This list is used to filter stories to which the user can view, normally by their module access
     *
     * @return array
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