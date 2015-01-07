<?php
/*
 * Name: Swift Shipment
 * Description: Containers
 */

class SwiftShipment extends Eloquent {
    
    use \Illuminate\Database\Eloquent\SoftDeletingTrait;    
    use \Venturecraft\Revisionable\RevisionableTrait;
    use \Swift\ElasticSearchEventTrait;
    
    protected $table = "swift_shipment";
    
    protected $guarded = array('id');
    
    protected $fillable = array('type','volume','gross_weight','container_no');
    
    public $timestamps = true;
    
    public $dates = ['deleted_at'];
    
    public static $type = array(self::LCL=>'LCL',self::FCL_20=>'FCL 20"',self::FCL_40=>'FCL 40"');
    
    //Shipment Types
    const LCL = 1;
    const FCL_20 = 2;
    const FCL_40 = 3;
    
    /* Revisionable Attributes */
    
    protected $revisionEnabled = true;
    
    protected $keepRevisionOf = array(
        'type','volume','gross_weight','container_no','deleted_at'
    );
    
    protected $revisionFormattedFieldNames = array(
        'shipment_type' => 'Type of Shipment',
        'volume' => 'Shipment Volume',
        'id'    => 'ID',
        'gross_weight' => 'Gross Weight',
        'container_no' => 'Container Number'
    );
    
    public $keepCreateRevision = true;  
    public $softDelete = true;
    public $revisionClassName = "Shipment";    
    public $revisionPrimaryIdentifier = "id";
    
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
        return "shipment";
    }
    
    /*
     * Event Observers
     */
    
    public static function boot() {
        parent:: boot();
        
        static::bootElasticSearchEvent();
        
        static::bootRevisionable();
    }
    
    public function getTypeRevisionAttribute($val)
    {
        if(key_exists($val,self::$type))
        {
            return self::$type[$val];
        }
        else
        {
            return "";
        }         
    }
    
    /*
     * Relationships
     */
    
    public function order()
    {
        return $this->belongsTo('SwiftOrder','order_id');
    }    
}