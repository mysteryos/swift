<?php
/*
 * Name: Swift Delivery
 * Description: Records all Delivery that has been done by store
 */

class SwiftDelivery extends Eloquent {
    use Illuminate\Database\Eloquent\SoftDeletingTrait;
    use \Venturecraft\Revisionable\RevisionableTrait; 
    use \Swift\ElasticSearchEventTrait;
    
    protected $table = "swift_delivery";
    
    protected $guarded = array('id');
    
    protected $fillable = array('status','status_comment','invoice_number','invoice_recipient','delivery_person','delivery_date','deliverable_type','deliverable_id');
    
    protected $dates = ['deleted_at','delivery_date'];
    
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
     * Elastic Search Indexing
     */
    
    //Indexing Enabled
    public $esEnabled = true;
    public $esInfoContext = "delivery";
    public $esExcludes = array('created_at','updated_at','deleted_at','status_comment','deliverable_type','deliverable_id');
    
    /*
     * ElasticSearch Utility Id
     */
    
    public function esGetId()
    {
        return $this->deliverable_id;
    }
    
    //Context for Indexing
    public function esGetContext()
    {
        switch($this->deliverable_type)
        {
            case "SwiftAPRequest":
                return "aprequest";
                break;
            default:
                return false;
        }
    }
    
    public function getStatusEsAttribute($val)
    {
        return $this->getStatusRevisionAttribute($val);
    }
    
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
     * Event Observers
     */
    
    public static function boot() {
        parent:: boot();
        
        static::bootElasticSearchEvent();
        
        static::bootRevisionable();
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

