<?php

/* 
 * Name: Swift Node Permission
 */

Class SwiftNodePermission extends Eloquent 
{
    
    protected $table = 'swift_node_permission';
    
    protected $guarded = array('id');
    
    protected $fillable = array('node_type_id','permission_name','permission_type');
    
    public $timestamps = false;
    
    /*
     * Relationship: Node
     */
    
    public function node()
    {
        return $this->belongsTo('SwiftNodeType','node_type_id');
    }
}
