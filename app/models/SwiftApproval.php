<?php
/*
 * Name: Swift Validation
 * Description: 
 */

class SwiftApproval extends Eloquent {
    use Illuminate\Database\Eloquent\SoftDeletingTrait;
    
    protected $table = "swift_approval";
    
    protected $guarded = array('id');
    
    protected $fillable = array('type','approved','approval_user_id');
    
    protected $dates = ['deleted_at'];
    
    public $timestamps = true;
    
    
    /*
     * PolyMorphic Relationships
     */
    
    public function approvable()
    {
        return $this->morphTo();
    }
    
    public function approduct()
    {
        return $this->morphByMany('SwiftApProduct','approvable');
    }
}
