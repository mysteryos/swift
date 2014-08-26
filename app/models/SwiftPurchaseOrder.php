<?php
/*
 * Name: Swift Purchase Orders
 * Description:
 */

use Illuminate\Database\Eloquent\SoftDeletingTrait;

class SwiftPurchaseOrder extends Eloquent {
    
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
        'reference','published_at'
    );
    
    protected $keepCreateRevision = true;
    
    protected $revisionFormattedFieldNames = array(
        'reference' => 'Purchase Order No',
        'published_at' => 'PO - Published at'
    );
    
    /*
     * Relationships
     */
    
    public function order()
    {
        return $this->belongsTo('SwiftOrder','order_id');
    }
}