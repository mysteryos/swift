<?php

Class SwiftSubscription extends Eloquent {
    protected $table = "swift_subscription";
    
    protected $guarded = array('id');
    
    protected $fields = array('id','user_id','status','subscriptionable_type','subscriptionable_id');
    
    const ACTIVE = 1;
    const INACTIVE = 0;
    
    public function scopeActive()
    {
        return $this->where('status','=',self::ACTIVE);
    }
    
    public function scopeInactive()
    {
        return $this->where('status','=',self::INACTIVE);
    }
    
    public function subscriptionable()
    {
        return $this->morphTo();
    }
    
    public static function getByClassAndUser($class_name,$class_id,$user_id)
    {
        return self::where('subscriptionable_type','=',$class_name)
                ->where('subscriptionable_id','=',$class_id,'AND')
                ->where('user_id','=',$user_id);
    }
    
}