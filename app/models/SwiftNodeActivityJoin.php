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
        $this->hasOne('SwiftNodeActivity','parent_id','id');
    }
    
    public function childNode()
    {
        $this->hasOne('SwiftNodeActivity','children_id','id');
    }
}

