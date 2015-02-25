<?php

class SwiftEvent extends Eloquent 
{
    
    use Illuminate\Database\Eloquent\SoftDeletingTrait;    
    
    protected $table = "swift_event";
    
    protected $guarded = array('id');
    
    protected $fillable = array('type_id','user_id','eventable_id','eventable_type');
    
    public $timestamps = true;
    
    protected $dates = ['deleted_at'];
    
    /*
     * Polymorphic
     */
    
    public function eventable()
    {
        return $this->morphTo();
    }
    
    public function type()
    {
        return $this->hasOne('SwiftEventType','type_id');
    }
    
    public function permission()
    {
        return $this->hasManyThrough('SwiftEventTypePermission','SwiftEventType','type_id','type_id');
    }
}