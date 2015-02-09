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
     * Scopes
     */
    
    public function scopeInprogress($query)
    {
        return $query->where('status','=',self::INPROGRESS);
    }
    
    public function scopeComplete($query)
    {
        return $query->where('status','=',self::COMPLETE);
    }
    
    public function scopeRejected($query)
    {
        return $query->where('status','=',self::REJECTED);
    }    
    
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
    
    public static function getInProgressResponsible($classList=array(),$perPage=0)
    {
        $query = self::query();
        
        if(!is_array($classList))
        {
            $classList = array($classList);
        }
        
        if(!empty($classList))
        {
            $query->whereIn('workflowable_type',$classList);
        }        
        
        $query->inprogress()
                ->whereIn('workflowable_type',$classList)
                ->with('nodes','workflowable')
                ->whereHas('nodes',function($q){
                    return $q->where('user_id','=',0)->whereHas('permission',function($q){
                        return $q->where('permission_type','=',SwiftNodePermission::RESPONSIBLE,'AND')
                               ->whereIn('permission_name',(array)array_keys(Sentry::getUser()->getMergedPermissions()));
                    });
                })
                ->orderBy('updated_at','DESC');
        
        if($perPage > 0)
        {
            return $query->simplePaginate($perPage);
        }
        else
        {
            return $query->get();
        }                
    }
    
    public static function getInProgress($classList=array(),$perPage=0)
    {
        $query = self::query();
        
        if(!is_array($classList))
        {
            $classList = array($classList);
        }
        
        if(!empty($classList))
        {
            $query->whereIn('workflowable_type',$classList);
        }
        
        $query->inprogress()
                    ->with('nodes','workflowable')
                    ->orderBy('updated_at','DESC')
                    ->remember(1);
        
        if($perPage > 0 )
        {
            return $query->simplePaginate($perPage);
        }
        else
        {
            return $query->get();
        }
    }
}