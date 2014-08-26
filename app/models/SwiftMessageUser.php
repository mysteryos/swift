<?php
/*
 * Name: Swift Message User
 * Description:
 */

class SwiftMessageUser extends Eloquent {
    protected $table = "swift_message_user";
    
    protected $guarded = array('id');
    
    protected $fillable = array('message_id','type','user_id','folder_id','unread');
    
    public $timestamps = true;
    
    public static $TYPE_SENDER = 1;
    public static $TYPE_RECEIVER = 2;
    
    public function folder()
    {
        return $this->hasOne('SwiftMessageFolder','id','folder_id');
    }
    
    public function message()
    {
        return $this->hasOne('SwiftMessage','id','message_id');
    }
    
    public function user()
    {
        return $this->hasOne('User','id','user_id');
    }
}