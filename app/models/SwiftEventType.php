<?php

class SwiftEventType extends Eloquent {
    protected $table = "swift_event_type";
    
    protected $guarded = array('id');
    
    protected $fillable = array('name','actionable','relation','weight');
    
    /*
     * Actionable constants
     */
    const ACTION = 1;
    const PASSIVE = 0;
    
    public function permission()
    {
        return $this->hasMany('SwiftEventTypePermission','type_id');
    }
}
