<?php

class SwiftStorage extends Eloquent {
    use Illuminate\Database\Eloquent\SoftDeletingTrait;
    use \Venturecraft\Revisionable\RevisionableTrait;
    use \Swift\ElasticSearchEventTrait;
    
    protected $table = "swift_storage";
    
    protected $guarded = array('id');
    
    protected $fillable = array('storage_start','demurrage_start','invoice_no','storage_charges','demurrage_charges','reason');
    
    public $timestamps = true;
    
    protected $touches = array('order');
    
    protected $dates = ['deleted_at','storage_start','demurrage_start'];
    
    /* Revisionable Attributes */
    
    protected $revisionEnabled = true;
    
    protected $keepRevisionOf = array(
        'storage_start','demurrage_start','invoice_no','storage_charges','demurrage_charges','reason'
    );
    
    protected $revisionFormattedFieldNames = array(
        'storage_start' => 'Storage Start Date',
        'demurrage_start' => 'Demurrage Start Date',
        'invoice_no' => 'Invoice Number',
        'storage_charges' => 'Storage Charges',
        'demurrage_charges' => 'Demurrage Charges',
        'reason' => 'Reason'
    );
    
    public $keepCreateRevision = true;
    public $softDelete = true;
    public $revisionClassName = "Storage";
    public $revisionPrimaryIdentifier = "id";
    
    /*
     * Elastic Search Indexing
     */
    
    //Indexing Enabled
    public $esEnabled = true;
    //Context for Indexing
    public $esContext = "order-tracking";
    public $esInfoContext = "storage";
    public $esRemove = ['order_id'];

    public function esGetParent()
    {
        return $this->order;
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
     * Relationships
     */
    
    public function order() {
        return $this->belongsTo('SwiftOrder','order_id');
    }
    
}