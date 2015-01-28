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
     * Permission Type Constants
     */
    
    const ACCESS = 1;
    const RESPONSIBLE = 2;
    
    /*
     * Query Scopes
     */
    
    public function scopeResponsible($query)
    {
        return $query->where('permission_type','=',self::RESPONSIBLE);
    }
    
    /*
     * Relationship: Node
     */
    
    public function node()
    {
        return $this->belongsTo('SwiftNodeType','node_type_id');
    }
    
    public static function getByPermission($permission_name,$type=1)
    {
        return self::whereIn('permission_name',array_keys($permission_name),'or')->where('permission_type','=',$type)->get();
    }
}
