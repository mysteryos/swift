<?php

class SwiftEventTypePermission extends Eloquent {
    protected $table = "swift_event_type_permission";
    
    protected $guarded = array('id');
    
    protected $fillable = array('type_id','permission_name');
    
    public function eventtype()
    {
        return $this->belongTo('SwiftEventType','type_id');
    }
}