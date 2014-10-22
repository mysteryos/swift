<?php
/*
 * Name: Swift Purchase Orders
 * Description:
 */

class SwiftPurchaseOrder extends Eloquent {
    use Illuminate\Database\Eloquent\SoftDeletingTrait;
    use \Venturecraft\Revisionable\RevisionableTrait;
    
    protected $table = "swift_purchase_order";
    
    protected $guarded = array('id');
    
    protected $fillable = array('order_id','reference','published_at');
    
    public $timestamps = true;
    
    protected $touches = array('order');
    
    public $dates = ['deleted_at','published_at'];
    
    /* Revisionable Attributes */
    
    protected $revisionEnabled = true;
    
    protected $keepRevisionOf = array(
        'reference','published_at','deleted_at'
    );
    
    protected $revisionFormattedFieldNames = array(
        'reference' => 'Purchase Order No',
        'published_at' => 'PO - Published at'
    );
    
    public $keepCreateRevision = true;
    public $softDelete = true;    
    public $revisionClassName = "Purchase Order";
    public $revisionPrimaryIdentifier = "reference";
    
    /*
     * Relationships
     */
    
    public function order()
    {
        return $this->belongsTo('SwiftOrder','order_id');
    }
}