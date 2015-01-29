<?php
/* 
 * Name: Swift Node Activity
 * Description: Stores all node related activity
 */

class SwiftNodeActivity extends Eloquent
{
    use Illuminate\Database\Eloquent\SoftDeletingTrait;
    
    protected $table = "swift_node_activity";
    
    protected $guarded = array('id');
    
    protected $fillable = array('node_definition_id','parent_id','workflow_activity_id','user_id','flow');
    
    public $timestamps = true;
    
    protected $dates = ['deleted_at'];
    
    protected $touches = array('workflowactivity');
    
    private $cachedQueries = array('countPendingNodesWithEta','getLateNodes','getPendingNodes');
    
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
     * Query Scope
     */
    
    public function scopeInprogress($query)
    {
        return $query->where('user_id','=',0);
    }
    
    public function scopeComplete($query)
    {
        return $query->where('user_id','>',0);
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
    
    public function workflowactivity()
    {
        return $this->belongsTo('SwiftWorkflowActivity','workflow_activity_id');
    }
    
    public function permission()
    {
        return $this->hasMany('SwiftNodePermission','node_definition_id','node_definition_id');
    }
    
    public function story()
    {
        return $this->morphMany('SwiftStory','storyfiable');
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
        return self::where('workflow_activity_id','=',$workflow_activity_id)->orderBy('created_at','desc')->get();
    }
    
    public static function getLateNodes($workflowType)
    {
        if(!is_array($workflowType))
        {
            $workflowType = array($workflowType);
        }
        
        $query = self::query();
        
        return $query->whereHas('definition',function($q){
                    return $q->where('eta','>',0);
                })
                ->inprogress()
                ->whereHas('workflowactivity',function($q) use ($workflowType){
                    return $q->whereHas('type',function($q) use ($workflowType){
                        return $q->whereIn('name',$workflowType);
                    })
                    ->where('status','=',\SwiftWorkflowActivity::INPROGRESS);
                })
                ->with(array('workflowactivity.workflowable','definition','permission' => function($q) {
                    return $q->responsible();
                }))
                ->orderBy('updated_at','ASC')->remember(30)->get();
                
    }
    
    //@use: WorkflowActivity::statusByType
    
    public static function getPendingNodes($workflowType)
    {
        if(!is_array($workflowType))
        {
            $workflowType = array($workflowType);
        }
        
        $query = self::query();        
        
        return $query->inprogress()
                    ->whereHas('workflowactivity',function($q) use ($workflowType){
                            return $q->whereHas('type',function($q) use ($workflowType){
                                return $q->whereIn('name',$workflowType);
                            })
                            ->where('status','=',\SwiftWorkflowActivity::INPROGRESS);
                    })
                    ->join('swift_node_definition','swift_node_definition.id','=','swift_node_activity.node_definition_id')
                    ->where('swift_node_definition.eta','>',0)
                    ->groupBy('swift_node_activity.node_definition_id')
                    ->select(\DB::raw('count(*) as count,swift_node_activity.node_definition_id,swift_node_definition.label,MIN(swift_node_activity.created_at) as min_created_at'))
                    ->remember(30)
                    ->get();        
  }
    
    public static function countPendingNodesWithEta($workflowType)
    {
        if(!is_array($workflowType))
        {
            $workflowType = array($workflowType);
        }
        
        return self::whereHas('definition',function($q){
                    return $q->where('eta','>',0);
                })
                ->inprogress()
                ->whereHas('workflowactivity',function($q) use ($workflowType){
                    return $q->inprogress()->whereHas('type',function($q) use ($workflowType){
                        return $q->whereIn('name',$workflowType);
                    });
                })->remember(30)->count();        
    }
}
