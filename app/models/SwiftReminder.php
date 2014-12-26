<?php
/*
 * Name: Swift Reminders
 * Description: To throttle reminders
 */

class SwiftReminder extends Eloquent {
    protected $table = "reminders";
    
    protected $fillable = array('type','user_id');
    
    protected $guarded = array('id');
    
    public $timestamps = true;
    
    /*
     * Types of Reminders
     */
    
    public static $APREQUEST_LATE = 1;
    
    /*
     * Relationships
     */
    
    public function user()
    {
        return $this->belongsTo('User','user_id');
    }
    
    public function remindable()
    {
        return $this->morphTo();
    }
}

