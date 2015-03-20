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
    
    protected $fillable = array('reference','type');
    
    public $timestamps = true;
    
    public $dates = ['deleted_at'];

    protected $attributes = [
        'type' => 'OF'
    ];

    //Types

    public static $types = [
        'OF' => 'OF',
        'ON' => 'ON',
        'OP' => 'OP',
        'OT' => 'OT',
    ];
    
    /* Revisionable Attributes */
    
    protected $revisionEnabled = true;
    
    protected $keepRevisionOf = array(
        'reference','type','deleted_at'
    );
    
    protected $revisionFormattedFieldNames = array(
        'reference' => 'Purchase Order No',
        'type'  => 'Purchase Order Type'
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
    public $esInfoContext = "purchaseOrder";
    public $esRemove = ['purchasable_id','purchasable_type'];
    
    /*
     * ElasticSearch Get Parent
     */
    
    public function esGetParent()
    {
        return $this->purchasable;
    }
    
    public function esGetContext() {
        return array_search($this->purchasable_type,Config::get('context'));
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
    
    public function purchasable()
    {
        return $this->morphTo();
    }
}