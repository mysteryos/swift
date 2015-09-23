<?php
/**
 * Description: Accounts Payable - Payment Details
 *
 * @author kpudaruth
 */

class SwiftACPPayment extends Eloquent
{
    use \Illuminate\Database\Eloquent\SoftDeletingTrait;
    use \Venturecraft\Revisionable\RevisionableTrait;
    use \Swift\ElasticSearchEventTrait;

    public $readableName = "Accounts Payable";

    protected $table = "swift_acp_payment";
    
    protected $fillable = ['status','type','date','amount','cheque_dispatch','cheque_dispatch_comment','payment_number','batch_number','cheque_signator_id','cheque_exec_signator_id','currency_code'];
    
    protected $dates = ['deleted_at','date','validated_on'];

    protected $touches = array('acp');

    protected $appends = ['amount_formatted'];

    protected $attributes = [
        'currency_code' => 'MUR',
        'amount' => 0,
        'cheque_dispatch' => 0,
        'validated'=>0,
        'status' => self::STATUS_INPROGRESS
    ];

    //Status
    const STATUS_INPROGRESS = 1;
    const STATUS_ISSUED = 2;
    const STATUS_SIGNED = 3;
    const STATUS_SIGNED_BY_EXEC = 4;
    const STATUS_DISPATCHED = 5;
    
    public static $status = [
        self::STATUS_INPROGRESS => 'In Progress',
        self::STATUS_ISSUED => 'Issued',
        self::STATUS_SIGNED => 'Signed by Accounting',
        self::STATUS_SIGNED_BY_EXEC=> 'Signed by Executive',
        self::STATUS_DISPATCHED => 'Dispatched'
    ];

    //Type
    const TYPE_CHEQUE = 1;
    const TYPE_BANKTRANSFER = 2;
    const TYPE_DIRECTDEBIT = 3;

    public static $type = [
        self::TYPE_CHEQUE => 'Cheque',
        self::TYPE_BANKTRANSFER => 'Bank Transfer',
        self::TYPE_DIRECTDEBIT => 'Direct Debit'
    ];

    //Validation
    const VALIDATION_PENDING = 0;
    const VALIDATION_COMPLETE = 1;
    const VALIDATION_ERROR = -1;

    public static $validationArray = [
        self::VALIDATION_PENDING => 'Pending',
        self::VALIDATION_COMPLETE => 'Complete',
        self::VALIDATION_ERROR => 'Error'
    ];

    //Dispatch

    const DISPATCH_PICKUP = 1;
    const DISPATCH_POSTAL = 2;
    const DISPATCH_RECEPTION = 3;

    public static $dispatch = [
        self::DISPATCH_PICKUP => 'Pickup',
        self::DISPATCH_POSTAL => 'Postal',
        self::DISPATCH_RECEPTION => 'Reception'
    ];
    
    /* Revisionable */
    
    protected $revisionEnabled = true;
    
    protected $keepRevisionOf = array('status','type','date','amount','currency_code','cheque_dispatch','cheque_dispatch_comment','payment_number','batch_number','cheque_signator_id','cheque_exec_signator_id');
    
    protected $revisionFormattedFieldNames = array(
        'id' => 'Id',
        'status' => 'Status',
        'type'  => 'Type',
        'date' => 'Date Due',
        'amount' => 'Amount',
        'cheque_dispatch' => 'Cheque Dispatch',
        'cheque_dispatch_comment' => 'Cheque Comment',
        'payment_number' => 'Payment Number',
        'batch_number' => 'Batch Number',
        'cheque_signator_id' => 'Cheque Signator',
        'cheque_exec_signator_id' => 'Executive Cheque Signator',
        'currency_code' => 'Currency'
    );
    
    public $saveCreateRevision = true;
    public $softDelete = true;
    public $revisionClassName = "Payment";
    public $revisionPrimaryIdentifier = "id";
    
    /* Elastic Search */
    
    //Indexing Enabled
    public $esEnabled = true;
    //Context for Indexing
    public $esContext = "acpayable";
    //Info Context
    public $esInfoContext = "payment";
    public $esRemove = ['cheque_dispatch_comment','acp_id','validated','validated_msg','validated_id','cheque_signator_id','cheque_exec_signator_id','amount_formatted'];

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

    public function getChequeDispatchRevisionAttribute($val)
    {
        if(key_exists($val,self::$dispatch))
        {
            return self::$dispatch[$val];
        }
        else
        {
            return "";
        }
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

    public function getStatusRevisionAttribute($val)
    {
        if(key_exists($val,self::$status))
        {
            return self::$status[$val];
        }
        else
        {
            return "";
        }
    }

    public function getChequeSignatorIdRevisionAttribute($val)
    {
        if((int)$val > 0)
        {
            $user = \User::find($val);
            return $user->first_name." ".$user->last_name;
        }

        return "";
    }
    
    public function getChequeExecSignatorIdRevisionAttribute($val)
    {
        if((int)$val > 0)
        {
            $user = \User::find($val);
            return $user->first_name." ".$user->last_name;
        }

        return "";
    }

    public function getChequeDispatchEsAttribute($val)
    {
        return $this->getChequeDispatchRevisionAttribute($val);
    }

    public function getTypeEsAttribute($val)
    {
        return $this->getTypeRevisionAttribute($val);
    }

    public function getStatusEsAttribute($val)
    {
        return $this->getStatusRevisionAttribute($val);
    }

    public function getAmountFormattedAttribute()
    {
        if(!empty($this->currency_code))
        {
            return $this->currency_code." ".number_format($this->amount);
        }

        return number_format($this->amount);
    }

    /*
     * Scope
     */

    public function scopeCheque($query)
    {
        return $query->where('type','=',self::TYPE_CHEQUE);
    }

    public function scopeBankTransfer($query)
    {
        return $query->where('type','=',self::TYPE_BANKTRANSFER);
    }

    public function scopeDirectDebit($query)
    {
        return $query->where('type','=',self::TYPE_DIRECTDEBIT);
    }
    
    /*
     * Relationships
     */

    public function acp()
    {
        return $this->belongsTo('SwiftACPRequest','acp_id');
    }

    public function currency()
    {
        return $this->belongsTo('Currency','currency_code');
    }

    public function chequeSignator()
    {
        return $this->belongsTo('User','cheque_signator_id');
    }

    public function chequeExecSignator()
    {
        return $this->belongsTo('User','cheque_exec_signator_id');
    }

    public function invoice()
    {
        return $this->hasOne('SwiftACPInvoice','acp_id','acp_id');
    }

    public function jdePaymentHeader()
    {
        return $this->hasOne('JdePaymentHeader','pyid','validated_id');
    }

    public function jdePaymentDetail()
    {
        return $this->hasMany('JdePaymentDetail','pyid','validated_id');
    }

    /*
     * Utility Functions
     */

    public function getClassName()
    {
        return $this->revisionClassName;
    }

    public function getReadableName()
    {
        return $this->name." (Id:".$this->id.")";
    }

    public function getIcon()
    {
        return "fa-money";
    }
    
    /*
     * Query
     */

    public static function sumTotalAmountPaid($acp_id)
    {
        return self::where('acp_id','=',$acp_id)
            ->groupBy('acp_id')
            ->whereNull('deleted_at')
            ->sum('amount');
    }
    
}
