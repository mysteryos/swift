<?php
/* 
 * Name: Swift Node Activity
 * Description: Stores all node related activity
 */

use Illuminate\Database\Eloquent\SoftDeletingTrait;

class SwiftNodeActivity extends Eloquent
{
    protected $table = "swift_node_activity";
    
    protected $guarded = array('id');
    
    protected $fillable = array('node_definition_id','parent_id','workflow_activity_id','user_id','flow');
    
    public $timestamps = true;
    
    protected $dates = ['deleted_at'];
    
    protected $touches = array('workflow_activity');
    
    /*
     * Flow Constants
     */
    public static $FLOW_FORWARD = 1;
    
    public static $FLOW_BACKWARD = -1;
    
    public static $FLOW_STOP = 0;
    
    
    /*
     * Events
     */
    
    public static function boot () {
        parent::boot();
        
        static::creating(function($model){
            if(is_null($model->user_id))
            {
                $model->user_id = 0;
            }
        });
    }
    
    /*
     * Relationship: Parent
     * Class: Self
     */
    
    public function parent()
    {
        return $this->belongsTo('SwiftNodeActivity', 'parent_id');
    }
    
    /*
     * Relationship: Children
     * Class: Self
     */

    public function children()
    {
        return $this->hasMany('SwiftNodeActivity', 'parent_id');
    }
    
    /*
     * Relationship: Definition
     * Class: SwiftNodeDefinition
     */
    
    public function definition()
    {
        return $this->hasOne('SwiftNodeDefinition','id','node_definition_id');
    }
    
    public function workflow_activity()
    {
        return $this->belongsTo('SwiftWorkflowActivity','workflow_activity_id');
    }
    
    /*
     * Get all nodes which needs to be processed
     */
    public static function inProgress($workflow_activity_id)
    {
        return self::whereRaw('user_id = 0 AND workflow_activity_id = ?',array($workflow_activity_id))->get();
    }
    
    public static function getByWorkflow($workflow_activity_id,$orderBy='asc')
    {
        return self::where('workflow_activity_id','=',$workflow_activity_id)->orderBy('created_at',$orderBy)->get();
    }
    
    /**
     * 
     * @param type $workflow_activity_id
     * @param type $node_definition_id
     * @return SwiftNodeActivity Object
     */
    public static function getByWorkflowAndDefinition($workflow_activity_id,$node_definition_id)
    {
        return self::whereRaw('workflow_activity_id = ? AND node_definition_id = ?',array($workflow_activity_id,$node_definition_id))->first();
    }
    
    public static function getLatestByWorkflow($workflow_activity_id)
    {
        return self::where('workflow_activity_id','=',$workflow_activity_id)->orderBy('updated_at','desc')->get();
    }
}
