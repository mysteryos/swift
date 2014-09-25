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
     * Relationships
     */
    public function aprequest()
    {
        return $this->belongTo('SwiftApRequest','aprequest_id');
    }
    
    public function approval()
    {
        return $this->morphMany('SwiftApproval','approvable');        
    }
    
    public function jdeproduct()
    {
        return $this->belongsTo('JdeProduct','LITM','jde_id');
    }
}
