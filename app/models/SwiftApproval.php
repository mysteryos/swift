<?php
/*
 * Name: Swift Validation
 * Description: 
 */

class SwiftApproval extends Eloquent {
    use Illuminate\Database\Eloquent\SoftDeletingTrait;
    use \Venturecraft\Revisionable\RevisionableTrait;
    
    protected $table = "swift_approval";
    
    protected $guarded = array('id');
    
    protected $fillable = array('type','approved','approval_user_id');
    
    protected $dates = ['deleted_at'];
    
    public $timestamps = true;
    
    
    /* Revisionable */
    
    protected $revisionEnabled = true;
    
    protected $keepRevisionOf = array(
        'approved'
    );
    
    protected $revisionFormattedFieldNames = array(
        'approved' => 'approval'
    );    
    
    public $revisionClassName = "A&P Order";
    public $revisionPrimaryIdentifier = "id";
    public $revisionPolymorphicIdentifier = "approvable";
    public $keepCreateRevision = true;
    public $softDelete = true;
    
    
    /*
     * Approval Types for AP Request
     */
    const APR_REQUESTER = 1;
    const APR_CATMAN = 2;
    const APR_EXEC = 3;
    const PR_RETAILMAN = 4;
    const APC_HOD = 5;
    const APC_PAYMENT = 6;
    const APC_REQUESTER = 7;
    
    /*
     * Approved Constants
     */
    
    const PENDING = 0;
    const APPROVED = 1;
    const REJECTED = -1;
    
    public static $approved = array(
                                self::PENDING => 'Pending',
                                self::APPROVED => 'Approved',
                                self::REJECTED => 'Rejected'
                                );
    
    /*
     * Revision Accessors
     */
    
    public function getApprovedRevisionAttribute($val)
    {
        if(key_exists($val,self::$approved))
        {
            return self::$approved[$val];
        }
        else
        {
            return "";
        }        
    }
    
    /*
     * PolyMorphic Relationships
     */
    
    public function approvable()
    {
        return $this->morphTo();
    }
 
    public function comment()
    {
        return $this->morphOne('SwiftComment', 'commentable');
    }
    
    public function comments()
    {
        return $this->morphMany('SwiftComment', 'commentable');
    }
    
    public function approduct()
    {
        return $this->morphByMany('SwiftAPProduct','approvable');
    }
    
    public function aprequest()
    {
        return $this->morphByMany('SwiftAPRequest','approvable');
    }
}
