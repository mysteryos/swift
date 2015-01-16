<?php

/* 
 * Name: Swift Node Type
 */

use Illuminate\Database\Eloquent\SoftDeletingTrait;

class SwiftWorkflowActivity extends Eloquent {
    
    use SoftDeletingTrait;
    use \Venturecraft\Revisionable\RevisionableTrait;
    
    protected $table = 'swift_workflow_activity';
    
    protected $guarded = array('id');
    
    protected $fillable = array('workflow_type_id','status','workflowable_type');
    
    protected $dates = ['deleted_at'];
    
    public $timestamps = true;
    
    /*
     * Constants
     */
    
    const REJECTED = -1;
    const COMPLETE = 1;
    const INPROGRESS = 0;
    
    /* Revisionable Attributes */
    
    protected $revisionEnabled = true;
    
    protected $keepRevisionOf = array(
        'status','reference','published_at'
    );
    
    protected $keepCreateRevision = true;    
    
    /*
     * Relationship: Workflow
     */
    
    public function type()
    {
        return $this->belongsTo('SwiftWorkflowType','workflow_type_id');
    }
    
    public function nodes()
    {
        return $this->hasMany('SwiftNodeActivity','workflow_activity_id');
    }
    
    public function pendingNodes()
    {
        return $this->hasMany('SwiftNodeActivity','workflow_activity_id')->where('user_id','=',0);
    }
    
    
    /*
     * Polymorphic Relation
     */
    
    public function workflowable()
    {
        return $this->morphTo();
    }
    
    public function order()
    {
        return $this->morphOne('SwiftOrder','workflowable');
    }
    
    public function aprequest()
    {
        return $this->morphOne('SwiftAPRequest','workflowable');
    }
    
    public function story()
    {
        return $this->morphMany('SwiftStory','storyfiable');
    }
}