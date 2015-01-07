<?php
/*
 * Name: Swift Purchase Orders
 * Description:
 */

class SwiftPurchaseOrder extends Eloquent {
    use Illuminate\Database\Eloquent\SoftDeletingTrait;
    use \Venturecraft\Revisionable\RevisionableTrait;
    use \Swift\ElasticSearchEventTrait;
    
    protected $table = "swift_purchase_order";
    
    protected $guarded = array('id');
    
    protected $fillable = array('order_id','reference');
    
    public $timestamps = true;
    
    protected $touches = array('order');
    
    public $dates = ['deleted_at'];
    
    /* Revisionable Attributes */
    
    protected $revisionEnabled = true;
    
    protected $keepRevisionOf = array(
        'reference','deleted_at'
    );
    
    protected $revisionFormattedFieldNames = array(
        'reference' => 'Purchase Order No',
    );
    
    public $keepCreateRevision = true;
    public $softDelete = true;    
    public $revisionClassName = "Purchase Order";
    public $revisionPrimaryIdentifier = "reference";
    
    /*
     * Elastic Search Indexing
     */
    
    //Indexing Enabled
    public $esEnabled = true;
    //Context for Indexing
    public $esContext = "order-tracking";    
    
    /*
     * ElasticSearch Utility Id
     */
    
    public function esGetId()
    {
        return $this->order_id;
    }
    
    public function esGetInfoContext()
    {
        return "purchaseOrder";
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
    
    public function order()
    {
        return $this->belongsTo('SwiftOrder','order_id');
    }
}