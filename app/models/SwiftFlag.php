<?php
/*
 * Name: Swift Flag
 */

class SwiftFlag extends eloquent {
    use Illuminate\Database\Eloquent\SoftDeletingTrait;
    
    protected $table = "swift_flag";
    
    protected $fillable = array('type','active');
    
    protected $guarded = array('id');
    
    public $timestamps = true;
    
    protected $dates = ['deleted_at'];
    
    
    //Active
    const ACTIVE = 1;
    const INACTIVE = 0;
    
    //Type
    const IMPORTANT = 1;
    const STARRED = 2;
    const READ = 3;
    
    public static function boot() {
        parent::boot();
        /*
         * Set User Id on create
         */
        static::creating(function($model){
            $model->user_id = Sentry::getUser()->id;
        });
    }
    
    /*
     * Relationships
     */
    
    public function flaggable()
    {
        return $this->morphTo();
    }
    
    public function order()
    {
        return $this->morphedByMany('SwiftOrder', 'flaggable');
    }    
}

