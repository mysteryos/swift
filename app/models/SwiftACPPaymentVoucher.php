<?php

/**
 * Description of SwiftACPPaymentVoucher
 *
 * @author kpudaruth
 */

class SwiftACPPaymentVoucher extends Eloquent
{
    use \Illuminate\Database\Eloquent\SoftDeletingTrait;
    use \Venturecraft\Revisionable\RevisionableTrait;
    use \Swift\ElasticSearchEventTrait;
    
    protected $table = "scott_swift.swift_acp_payment_voucher";
    
    protected $fillable = ['number'];
    
    protected $dates = ['deleted_at'];

    protected $touches = array('acp');
    
    /* Revisionable */
    
    protected $revisionEnabled = true;
    
    protected $keepRevisionOf = array('number');
    
    protected $revisionFormattedFieldNames = array(
        'number' => 'PV number'
    );
    
    public $saveCreateRevision = true;
    public $softDelete = true;
    public $revisionClassName =  "Accounts Payable Payment Voucher";
    public $revisionPrimaryIdentifier = "number";
    
    /* Elastic Search */
    
    //Indexing Enabled
    public $esEnabled = true;
    //Context for Indexing
    public $esContext = "acpayable";
    //Info Context
    public $esInfoContext = "paymentVoucher";
    public $esRemove = ['acp_id'];

    public function esGetParent()
    {
        return $this->acp;
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
     * Accessors
     */
    
    /*
     * Scope
     */
    
    /*
     * Relationships
     */

    public function acp()
    {
        return $this->belongsTo('SwiftACPRequest','acp_id');
    }
    
    /*
     * Query
     */
    
}
