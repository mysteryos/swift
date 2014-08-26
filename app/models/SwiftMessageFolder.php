<?php
/*
 * Name: Swift Message folder
 * Description:
 */

class SwiftMessageFolder extends eloquent {
    protected $table = "swift_message_folder";
    
    protected $guarded = array('id');
    
    protected $fillable = array('name','type','user_id');
    
    public $timestamps = true;
    
    public static $TYPE_SYSTEM = 1;
    public static $TYPE_CUSTOM = 2;
    
    public static $INBOX = 1;
    public static $SENT = 2;
    public static $TRASH = 3;
    public static $ARCHIVE = 4;
}