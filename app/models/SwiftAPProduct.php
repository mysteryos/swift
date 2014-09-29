<?php
/*
 * Name: Swift A&P Product
 * Description:
 */

class SwiftAPProduct extends Eloquent {
    use Illuminate\Database\Eloquent\SoftDeletingTrait;
    use \Venturecraft\Revisionable\RevisionableTrait; 
    
    protected $table = "swift_ap_product";
    
    protected $fillable = array("aprequest_id","jde_id","quantity","reason_code","reason_others");
    
    protected $guarded = array('id');
    
    public $timestamps = true;
    
    protected $dates = ['deleted_at'];
    
    /* Revisionable */
    
    protected $revisionEnabled = true;
    
    protected $keepRevisionOf = array(
        'jde_id','quantity','reason_code','reason_others'
    );
    
    protected $revisionFormattedFieldNames = array(
        'document_file_name' => 'Document Name',
        'jde_id' => 'Product JDE Id',
        'quantity' => 'Product Quantity',
        'reason_code' => 'Reason Code',
        'reason_others' => 'Reason(specify)',
    );    
    
    protected $revisionClassName = "A&P Product";
    protected $revisionPrimaryIdentifier = "id";
    protected $keepCreateRevision = true;
    protected $softDelete = true;
    
    
    
    /*
     * Constants : Reason Codes
     */
    
    const RC_EVENT = 1;
    const RC_TESTING = 2;
    const RC_SPONSOR = 3;
    const RC_TRAINING = 4;
    const RC_CONTRIBUTION = 5;
    
    public static $reason = array(self::RC_EVENT => 'Event',
                                  self::RC_TESTING => 'Testing/Tasting',
                                  self::RC_SPONSOR => 'Sponsorship',
                                  self::RC_TRAINING => 'Training',
                                  self::RC_CONTRIBUTION=> 'Contribution');
    
    /*
     * Events
     */
    
//    protected static function boot() {
//        parent::boot();
//        
//        static::deleting(function($this) { // called BEFORE delete()
//            //Remove all approvals associated with the product
//            $this->approval()->delete();
//        });
//        
//        static::bootRevisionable();
//        static::bootSoftDeletingTrait();
//    }
    
    /*
     * Revisionable Accessors
     */
    
    public function getReasonCodeRevisionAttribute($val)
    {
        if(key_exists($val,self::$reason))
        {
            return self::$reason[$val];
        }
        else
        {
            return "";
        }           
    }
    
    /*
     * Relationships
     */
    public function aprequest()
    {
        return $this->belongsTo('SwiftApRequest','aprequest_id');
    }
    
    public function approval()
    {
        return $this->morphMany('SwiftApproval','approvable');        
    }
    
    public function jdeproduct()
    {
        return $this->belongsTo('JdeProduct','jde_id','ITM');
    }
}
