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

    protected $attributes = [
        'validated' => self::VALIDATION_PENDING
    ];

    public static $validationArray = [
        self::VALIDATION_PENDING => 'Pending',
        self::VALIDATION_COMPLETE => 'Complete',
        self::VALIDATION_ERROR => 'Error'
    ];

    //Validation
    const VALIDATION_PENDING = 0;
    const VALIDATION_COMPLETE = 1;
    const VALIDATION_ERROR = -1;

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
    public $esRemove = ['acp_id','validated','validated_msg'];

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

        static::created(function($model){
            //Push job to validate PV in JDE table
        });
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

    public function invoice()
    {
        return $this->hasOne('SwiftACPInvoice','payment_voucher_id');
    }
    
    /*
     * Query
     */
    
}
