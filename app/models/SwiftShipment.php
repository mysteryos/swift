<?php
/*
 * Name: Swift Shipment
 * Description: Containers
 */

class SwiftShipment extends Eloquent {
    
    use \Illuminate\Database\Eloquent\SoftDeletingTrait;    
    use \Venturecraft\Revisionable\RevisionableTrait;
    
    protected $table = "swift_shipment";
    
    protected $guarded = array('id');
    
    protected $fillable = array('type','volume');
    
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
        'type','volume','deleted_at'
    );
    
    protected $revisionFormattedFieldNames = array(
        'shipment_type' => 'Type of Shipment',
        'volume' => 'Shipment Volume'
    );
    
    protected $keepCreateRevision = true;  
    protected $softDelete = true;
    
    protected $revisionClassName = "Shipment";    
    
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