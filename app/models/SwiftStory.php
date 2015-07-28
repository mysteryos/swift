<?php
/*
 * Name:
 * Description:
 */

class SwiftStory extends Eloquent {
    
    protected $table = "swift_story";
    
    protected $fields = array('action','by','context_type','context_id','type','view','storyfiable_type');
    
    protected $guarded = array('id');
    
    protected $attributes = array(
        'type' => self::TYPE_STATIC,
        'view' => 0
    );    
    
    
    //Actions
    const ACTION_CREATE = 1;
    const ACTION_UPDATE = 2;
    const ACTION_CANCEL = 3;
    const ACTION_COMMENT = 4;
    const ACTION_COMPLETE = 5;
    const ACTION_STATISTICS = 6;
    const ACTION_VESSEL = 7;
    
    //Context
    const NODE_ACTIVITY = "SwiftNodeActivity";
    const WORKFLOW_ACTIVITY = "SwiftWorkflowActivity";
    const ORDER_TRACKING = "SwiftOrder";
    const APREQUEST = "SwiftAPRequest";
    const ACPAYABLE = "SwiftACPRequest";
    const COMMENT = "SwiftComment";
    const PR = "SwiftPR";
    const VESSELLIVE = "CHCLLive";
    
    //Type
    const TYPE_STATIC = 1;
    
    
    /*
     * Event
     */
    public static function boot() {
        parent:: boot();
        
        /*
         * Set User Id on create
         */
        static::creating(function($model){
            $model->by = Sentry::getUser()->id;
        });
    }    
    
    /*
     * Relationships
     */
    
    public function byUser()
    {
        return $this->belongsTo('User','by');
    }    
    
    public function storyfiable()
    {
        return $this->morphTo()->withTrashed();
    }
    
    public function context()
    {
        return $this->morphTo()->withTrashed();
    }
    
    public function actionText()
    {
        $action = false;
        switch($this->action)
        {
            case self::ACTION_CREATE:
                $action = "created";
                break;
            case self::ACTION_UPDATE:
                $action = "updated";
                break;
            case self::ACTION_CANCEL:
                $action = "cancelled";
                break;
            case self::ACTION_COMPLETE:
                $action = "completed";
                break;
            case self::ACTION_COMMENT:
                $action = "commented on";
                break;
            case self::ACTION_STATISTICS:
                $action = "generated";
                break;
            case self::ACTION_VESSEL:
                $action = "foresaw the unloading of";
        }
        return $action;
    }
    
    public function contextText()
    {
        $context = false;
        switch($this->storyfiable_type)
        {
            case self::NODE_ACTIVITY:
                $context = "step <b>{$this->storyfiable->definition->label}</b> <a href=\"{$this->contextLink()}\" class=\"pjax\"><i class=\"fa {$this->storyfiable->workflowactivity->workflowable->getIcon()}\"></i> {$this->storyfiable->workflowactivity->workflowable->getReadableName()}</a>";
                break;
            case self::WORKFLOW_ACTIVITY:
                $context = "form <a href=\"{$this->contextLink()}\" class=\"pjax\"><i class=\"fa {$this->storyfiable->workflowable->getIcon()}\"></i> {$this->storyfiable->workflowable->getReadableName()}</a>";
                break;
            case self::ORDER_TRACKING:
            case self::APREQUEST:
            case self::ACPAYABLE:
            case self::PR:
                $context = "<a href=\"{$this->contextLink()}\" class=\"pjax\"><i class=\"fa {$this->storyfiable->getIcon()}\"></i> {$this->storyfiable->getReadableName()}</a>";
                break;
            case self::COMMENT:
                $context = "on <a href=\"{$this->contextLink()}\" class=\"pjax\"><i class=\"fa {$this->storyfiable->commentable->getIcon()}\"></i> {$this->storyfiable->commentable->getReadableName()}</a>: '{$this->storyfiable->comment}'";
                break;
            case self::VESSELLIVE:
                $context = "{$this->storyfiable->voyage} - <a href=\"{$this->contextLink()}\" class=\"pjax\"><i class=\"fa {$this->context->getIcon()}\"></i> {$this->context->getReadableName()}</a>";
                break;
        }
        return $context;
    }
    
    public function contextLink()
    {
        $link = false;
        switch($this->storyfiable_type)
        {
            case self::NODE_ACTIVITY:
                $link = \Helper::generateUrl($this->storyfiable->workflowactivity->workflowable);
                break;
            case self::WORKFLOW_ACTIVITY:
                $link = \Helper::generateUrl($this->storyfiable->workflowable);
                break;
            case self::ORDER_TRACKING:
            case self::APREQUEST:
            case self::ACPAYABLE:
            case self::PR:
                $link = \Helper::generateUrl($this->storyfiable);
                break;
            case self::COMMENT:
                $link = \Helper::generateUrl($this->storyfiable->commentable);
                break;
            case self::VESSELLIVE:
                $link = \Helper::generateUrl($this->context);
                break;
        }
        return $link;        
    }
    
}