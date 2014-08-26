<?php

/* 
 * Name: Swift Route
 * Description: Eloquent model
 */

class SwiftRoute extends Eloquent {
    
    protected $table = 'swift_route';
    
    public $timestamps = false;
    
    protected $guarded = array('id');
    
    protected $fillable = array('route');
    
    public function scopeRoute($query,$route)
    {
        return $this->whereRoute($route);
    }
    
}