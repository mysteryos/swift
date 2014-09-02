<?php
/*
 * Name: Swift Flag
 */

class SwiftFlag extends eloquent {
    use Illuminate\Database\Eloquent\SoftDeletingTrait;
    
    protected $table = "swift_flag";
    
    protected $fillable = array('type');
    
    protected $guarded = array('id');
    
    public $timestamps = true;
    
    protected $dates = ['deleted_at'];
    
    const IMPORTANT = 1;
    const STARRED = 2;
    
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

