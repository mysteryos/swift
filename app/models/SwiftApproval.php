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
     * Approval Types for AP Request
     */
    const APR_REQUESTER = 1;
    const APR_CATMAN = 2;
    const APR_EXEC = 3;
    
    /*
     * Approved Constants
     */
    
    const PENDING = 0;
    const APPROVED = 1;
    const REJECTED = -1;
    
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
    
    public function aprequest()
    {
        return $this->morphByMany('SwiftAPRequest','approvable');
    }
}
