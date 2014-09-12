<?php

/* 
 * Name: Swift Node Definition
 * Description: Stores all Node definition
 */

use \Illuminate\Database\Eloquent\SoftDeletingTrait;

class SwiftNodeDefinition extends Eloquent {
    
    use SoftDeletingTrait;
    
    protected $table = 'swift_node_definition';
    
    protected $guarded = array('id');
    
    protected $fillable = array('type','name','label','description','php_function','workflow_type_id','eta','data');
    
    protected $dates = ['deleted_at'];
    
    public $timestamps = true;
    
    /*
     * Type of Nodes
     */
    
    //Start Node
    public static $T_NODE_START = 1;
    //End Node
    public static $T_NODE_END = 2;
    //Kill Node - Stop Workflow
    public static $T_NODE_KILL = -1;
    //Fork Node - Start a fork Split
    //public static $T_NODE_SPLIT = 3;
    //Join Node - Forks join
    //public static $T_NODE_JOIN = 4;
    //Action Node - Executes code & returns true (always)
    public static $T_NODE_ACTION = 5;
    //Condition Node - Returns True on success
    public static $T_NODE_CONDITION = 6;
    //Input Node - Requires Input from User - Expects true
    public static $T_NODE_INPUT = 7;
    
    /*
     * Getter/Setter Methods for Data Field -- START
     */
    
    public function getDataAttribute($value)
    {
        return ($value == '' ? '' : json_decode($value));
    }
    
    public function setDataAttribute($value)
    {
        return ($value == '' ? '' : json_encode((array)$value));
    }
    
    /*
     * Getter/Setter Methods for Data Field -- END
     */    
    
    /*
     * Relationship: Workflow
     */
    
    public function workflow()
    {
        return $this->belongsTo('SwiftWorkflowType','workflow_type_id');
    }
    
    /*
     * Relationship: Permission
     */
    
    public function permission()
    {
        return $this->hasMany('SwiftNodePermission','node_definition_id');
    }
    
    /*
     * Relationship: Parent
     */
    
    public function parent()
    {
        return $this->hasMany('SwiftNodeDefinitionJoin', 'node_definition_id','id');        
    }
    
    /*
     * Relationship: Children
     */

    public function children()
    {
        return $this->belongsTo('SwiftNodeDefinitionJoin', 'parent_id','id');
    }
    
    
    /*
     * Fetch Node By Type
     */
    public static function getByType($workflow_type_id,$type)
    {
        return self::where('workflow_type_id','=', $workflow_type_id,'AND')->where('type','=',$type)->get();
    }
    
    /*
     * Fetch node by unique Name
     */
    public static function getByName($node_definition_name)
    {
        return self::where('name','=',$node_definition_name)->first();
    }
    
    /*
     * Get Next Node(s)
     */
    public static function getNext($node_definition_id)
    {
        return self::find($node_definition_id)->children()->childNode()->get();
    }
    
    /*
     * Get Previous Node(s)
     */
    public static function getPrevious($node_definition_id)
    {
        return self::find($node_definition_id)->parent()->parentNode()->get();
    }
    
    public static function getByWorkflowType($workflow_type_id)
    {
        return self::where('workflow_type_id','=',$workflow_type_id)
                    ->whereIn('type',array(SwiftNodeDefinition::$T_NODE_START,SwiftNodeDefinition::$T_NODE_END),'and',true)
                    ->get();
    }
}