<?php

/**
 * Description: Accounts Payable - Payment Voucher (aka Invoices on JDE)
 *
 * @author kpudaruth
 */

class SwiftACPPaymentVoucher extends Eloquent
{
    use \Illuminate\Database\Eloquent\SoftDeletingTrait;
    use \Venturecraft\Revisionable\RevisionableTrait;
    use \Swift\ElasticSearchEventTrait;
    
    protected $table = "scott_swift.swift_acp_payment_voucher";
    
    protected $fillable = ['number','type','validated'];
    
    protected $dates = ['deleted_at'];

    protected $touches = array('acp');

    protected $attributes = [
        'validated' => self::VALIDATION_PENDING,
        'type' => self::TYPE_PV
    ];

    public static $validationArray = [
        self::VALIDATION_PENDING => 'Pending',
        self::VALIDATION_COMPLETE => 'Complete',
        self::VALIDATION_ERROR => 'Error'
    ];

    public static $typeArray = [
        self::TYPE_PV => 'Invoice',
        self::TYPE_PD => 'Credit Note'
    ];

    //Validation
    const VALIDATION_PENDING = 0;
    const VALIDATION_COMPLETE = 1;
    const VALIDATION_ERROR = -1;

    //Type

    const TYPE_PV = 'PV';
    const TYPE_PD = 'PD';

    /* Revisionable */
    
    protected $revisionEnabled = true;
    
    protected $keepRevisionOf = array('number');
    
    protected $revisionFormattedFieldNames = array(
        'number' => 'PV number',
        'type' => 'Type'
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
    public $esRemove = ['acp_id','validated','validated_msg','type'];

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
        static::updating(function($model){
            $dirty = $model->getDirty();
            if(array_key_exists('number',$dirty))
            {
                $model->validated = self::VALIDATION_PENDING;
                $model->validated_msg = "";
            }
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
        return $this->hasOne('SwiftACPInvoice','acp_id','acp_id');
    }
    
    /*
     * Query
     */
    
}
