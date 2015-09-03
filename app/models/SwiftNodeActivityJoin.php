<?php
/* 
 * Name: Swift Node Activity Join
 */

class SwiftNodeActivityJoin extends Eloquent {
    
    protected $table = "swift_node_activity_join";
    
    protected $guarded = array('id');
    
    protected $fillable = array('parent_id','children_id');
    
    public $timestamps = false;
    
    public function parentNode()
    {
        return $this->belongsTo('SwiftNodeActivity','parent_id');
    }
    
    public function childNode()
    {
        return $this->belongsTo('SwiftNodeActivity','children_id');
    }
}

