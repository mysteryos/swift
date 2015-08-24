<?php
/*
 * Name:
 * Description:
 */

class SwiftRecent extends Eloquent {
    protected $table = "swift_recent";
    
    protected $guarded = array('id');
    
    protected $fillable = array('user_id','recentable_id','recentable_type','updated_at');
    
    public $timestamps = true;
    
    /*
     * Events
     */
    public static function boot() {
        parent::boot();
        /*
         * Set User Id on create
         */
        static::creating(function($model){
            $model->user_id = \Sentry::getUser()->id;
        });
    }    
    
    /*
     * PolyMorphic Relationships
     */
    
    public function recentable()
    {
        return $this->morphTo();
    }
    
    public function aprequest()
    {
        return $this->morphByMany('SwiftAPRequest','recentable');
    }
    
    public function order()
    {
        return $this->morphByMany('SwiftOrder','recentable');
    }
    
    public function user()
    {
            return $this->belongsTo('User');
    }    
}