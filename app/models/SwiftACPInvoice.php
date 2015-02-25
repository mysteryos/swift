<?php

/**
 * Description of SwiftACPInvoice: Account Payable Invoices
 *
 * @author kpudaruth
 */

class SwiftACPInvoice extends Eloquent
{
    use \Illuminate\Database\Eloquent\SoftDeletingTrait;
    use \Venturecraft\Revisionable\RevisionableTrait;
    use \Swift\ElasticSearchEventTrait;
    
    protected $table = "swift_acp_invoice";
    
    protected $fillable = ['number','date','due_date','due_amount','payment_term','currency','gl_code'];
    
    protected $dates = ['deleted_at','date','due_date'];

    protected $attributes = [
        'currency' => '96'
    ];

    //Payment Term
    const PAYMENT_CASH = 1;
    const PAYMENT_CHEQUE = 2;

    public static $paymentTerm = [
        self::PAYMENT_CASH => 'Cash',
        self::PAYMENT_CHEQUE => 'Cheque'
    ];

    /* Revisionable */
    
    protected $revisionEnabled = true;
    
    protected $keepRevisionOf = array();
    
    protected $revisionFormattedFieldNames = array();
    
    public $saveCreateRevision = true;
    public $softDelete = true;
    public $revisionClassName =  "Account Payable Invoice";
    public $revisionPrimaryIdentifier = "id";
    
    /* Elastic Search */
    
    //Indexing Enabled
    public $esEnabled = true;
    //Context for Indexing
    public $esContext = "acpayable";
    //Info Context
    public $esInfoContext = "invoice";
    public $esRemove = ["acp_id"];

    public function esGetParent()
    {
        return $this->ac;
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

    public function getCurrencyRevisionableAttribute($val)
    {
        $currency = Currency::find($val);
        if($currency)
        {
            return $currency->fullname;
        }

        return "(N/A)";
    }

    public function getCurrencyEsAttribute($val)
    {
        return $this->getCurrencyRevisionableAttribute($val);
    }

    public function getPaymentTermRevisionableAttribute($val)
    {
        if(key_exists($val,self::$paymentTerm))
        {
            return self::$paymentTerm[$val];
        }
        else
        {
            return "";
        }
    }

    public function getPaymentTermEsAttribute($val)
    {
        return $this->getPaymentTermRevisionableAttribute($val);
    }
    
    /*
     * Scope
     */
    
    /*
     * Relationships
     */

    public function ac()
    {
        return $this->belongsTo('SwiftACPRequest','ac_id');
    }
    
    /*
     * Query
     */
    
}
