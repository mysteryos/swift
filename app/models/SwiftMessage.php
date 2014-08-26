<?php
/*
 * Name: Message
 * Description: Message for the communicators
 */

class SwiftMessage extends Eloquent {
    
    /*
     * Eloquent Attributes
     */
    protected $table = "swift_message";
    
    protected $guarded = array('id');
    
    protected $fillable = array('author','content', 'subject', 'messageable_type', 'messageable_id');
    
    public $timestamps = true;
    
    /*
     * Relationships
     */
    
    public function messageable()
    {
        return $this->morphTo();
    }
    
    public function author()
    {
        return $this->hasOne('User','id','author');
    }
    
    public function messageUser()
    {
        return $this->belongsToMany('SwiftMessageUser','swift_message_user','message_id');
    }
}
