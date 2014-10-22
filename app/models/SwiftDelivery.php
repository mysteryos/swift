<?php
/*
 * Name: Swift Delivery
 * Description: Records all Delivery that has been done by store
 */

class SwiftDelivery extends Eloquent {
    use Illuminate\Database\Eloquent\SoftDeletingTrait;
    use \Venturecraft\Revisionable\RevisionableTrait; 
    
    protected $table = "swift_delivery";
    
    protected $guarded = array('id');
    
    protected $fillable = array('status','status_comment','invoice_number','invoice_recipient','delivery_person','delivery_date');
    
    protected $dates = ['deleted_at'];
    
    public $timestamps = true;
    
    /* Revisionable */
    
    protected $revisionEnabled = true;
    
    protected $keepRevisionOf = array(
        'status','invoice_number','invoice_recipient','delivery_person','delivery_date'
    );
    
    protected $revisionFormattedFieldNames = array(
        'status' => 'Delivery Status',
        'status_comment' => 'Delivery Comment',
        'invoice_number' => 'Invoice No',
        'invoice_recipient' => 'Invoice Recipient',
        'delivery_person' => 'Delivery Person',
        'delivery_date' => 'Delivery Date'
    );    
    
    public $revisionClassName = "Delivery";
    public $revisionPrimaryIdentifier = "invoice_number";    
    public $keepCreateRevision = true;
    public $softDelete = true;
    
    /*
     * constants
     */
    
    const CANCELLED = -1;
    const DELIVERED = 1;
    const PENDING = 0;
    
    public static $status = array(self::CANCELLED=>'Cancelled',
                                    self::DELIVERED=>'Delivered',
                                    self::PENDING=>'Pending');
    
    
    /*
     * Revisionable Functions
     */
    
    public function getStatusRevisionAttribute($val)
    {
        if(key_exists($val,self::$status))
        {
            return self::$status[$val];
        }
        else
        {
            return "";
        }        
    }
    
    /*
     * PolyMorphic Relationships
     */
    
    public function deliverable()
    {
        return $this->morphTo();
    }
    
    public function aprequest()
    {
        return $this->belongsTo('SwiftAPRequest');
    }
}

