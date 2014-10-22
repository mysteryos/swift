<?php
/*
 * Name: Swift Shipment Storage
 * Description: Stores Costs Associated to Storage
 */

class SwiftShipmentStorage extends Eloquent {
    
    use \Illuminate\Database\Eloquent\SoftDeletingTrait;
    use \Venturecraft\Revisionable\RevisionableTrait;
    
    protected $table = "swift_shipment_storage";
    
    protected $guarded = array('id');
    
    protected $fillable = array('rate','start','end');
    
    public $timestamps = true;
    
    public $dates = ['start,end,deleted_at'];
    
    /* Revisionable Attributes */
    
    protected $revisionEnabled = true;
    
    protected $keepRevisionOf = array(
        'rate','start','end'
    );
    
    protected $revisionFormattedFieldNames = array(
        'rate' => 'Exchange Rate',
        'start' => 'Storage Start',
        'end'    => 'Storage End',
    );
    
    public $keepCreateRevision = true;  
    public $softDelete = true;
    public $revisionClassName = "Storage";    
    public $revisionPrimaryIdentifier = "id";    
    
    /*
     * Relationships
     */
    
    public function order()
    {
        return $this->belongsTo('SwiftOrder','order_id');
    }     
}

