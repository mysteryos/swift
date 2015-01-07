<?php

class SwiftNotification extends Eloquent {
    protected $table = "swift_notification";
    
    protected $fields = array('to','from','msg','unread','type');
    
    protected $guarded = array('id');
    
    public $timestamps = true;
    
    /*
     * Unread
     */
    const READ = 0;
    const UNREAD = 1;
    
    /*
     * Type
     */
    
    const TYPE_RESPONSIBLE = 1;
    const TYPE_ACTION = 2;
    const TYPE_STATISTICS = 3;
    const TYPE_INFO = 4;
    const TYPE_SUCCESS = 5;
    const TYPE_COMMENT = 6;
    
    /*
     * Event
     */
    public static function boot() {
        parent:: boot();
        
        /*
         * Set User Id on create
         */
        static::creating(function($model){
            $model->unread = self::UNREAD;
        });
    }
    
    
    public function to()
    {
        return $this->belongsTo('User','to');
    }
    
    public function from()
    {
        return $this->belongsTo('User','from');
    }
    
    public function notifiable()
    {
        return $this->morphTo();
    }
    
    public static function getByUser($user_id,$limit=10)
    {
        return self::where('to','=',$user_id)->take($limit)->orderBy('created_at','DESC')->get();
    }
    
    public static function getUnreadCountByUser($user_id)
    {
        return self::where('to','=',$user_id)->where('unread','=',self::UNREAD,'AND')->count();
    }
    
    public static function setRead($user_id)
    {
        return self::where('to','=',$user_id)->where('unread','=',self::UNREAD,'AND')->update(array('unread'=>self::READ));
    }
}
