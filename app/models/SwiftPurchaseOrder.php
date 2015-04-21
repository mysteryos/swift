<?php
/*
 * Name: Swift Purchase Orders
 * Description:
 */

class SwiftPurchaseOrder extends Eloquent
{
    use Illuminate\Database\Eloquent\SoftDeletingTrait;
    use \Venturecraft\Revisionable\RevisionableTrait;
    use \Swift\ElasticSearchEventTrait;
    
    protected $table = "swift_purchase_order";
    
    protected $guarded = array('id');
    
    protected $fillable = array('reference','type');

    protected $hidden = array('validated','validated_on','order_id');
    
    public $timestamps = true;
    
    public $dates = ['deleted_at','validated_on'];

    protected $attributes = [
        'type' => 'OF',
        'validated' => 0
    ];

    //Types

    public static $types = [
        'OF' => 'OF',
        'ON' => 'ON',
        'OP' => 'OP',
        'OT' => 'OT',
    ];

    public static $validation = [
        self::VALIDATION_NOTFOUND_PERMANENT => "Not Found Permanent",
        self::VALIDATION_NOTFOUND => "Not Found",
        self::VALIDATION_PENDING => "Pending",
        self::VALIDATION_FOUND => "Found",
    ];

    const VALIDATION_NOTFOUND = -1;
    const VALIDATION_FOUND = 1;
    const VALIDATION_PENDING = 0;
    const VALIDATION_NOTFOUND_PERMANENT = -2;
    
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

        static::saving(function($model){
            foreach($model->getDirty() as $attribute => $value)
            {
                if(in_array($attribute,['reference','type']))
                {
                    if($model->reference !== null && $model->type !== null)
                    {
                        $model->validated = self::VALIDATION_PENDING;
                        \Queue::push('Helper@validatePendingPurchaseOrder');
                        break;
                    }
                }
            }
        });

        static::saved(function($model){
            if($model->validated === self::VALIDATION_PENDING && $model->reference !== null && $model->type !== null)
            {
                \Queue::push('Helper@validatePendingPurchaseOrder');
            }
        });
    }
    
    /*
     * Relationships
     */
    
    public function purchasable()
    {
        return $this->morphTo();
    }

    public function jdepo()
    {
        return $this->belongsTo('JdePurchaseOrder','order_id');
    }
}