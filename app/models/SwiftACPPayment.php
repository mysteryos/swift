<?php
/**
 * Description of SwiftACPPayment
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
    
    protected $fillable = ['status','type','date','amount','cheque_dispatch','cheque_dispatch_comment','journal_entry_number'];
    
    protected $dates = ['deleted_at','date'];

    //Status
    const STATUS_INPROGRESS = 1;
    const STATUS_ISSUED = 2;
    const STATUS_SIGNED = 3;
    const STATUS_DISPATCHED = 4;
    
    public static $status = [
        self::STATUS_INPROGRESS => 'In Progress',
        self::STATUS_ISSUED => 'Issued',
        self::STATUS_SIGNED => 'Signed',
        self::STATUS_DISPATCHED => 'Dispatched'
    ];

    //Type
    const TYPE_CHEQUE = 1;
    const TYPE_BANKTRANSFER = 2;

    public static $type = [
        self::TYPE_CHEQUE => 'Cheque',
        self::TYPE_BANKTRANSFER => 'Bank Transfer'
    ];

    //Dispatch

    const DISPATCH_PICKUP = 1;
    const DISPATCH_POSTAL = 2;

    public static $dispatch = [
        self::DISPATCH_PICKUP => 'Pickup',
        self::DISPATCH_POSTAL => 'Postal'
    ];
    
    /* Revisionable */
    
    protected $revisionEnabled = true;
    
    protected $keepRevisionOf = array('status','type','date','amount','cheque_dispatch','cheque_dispatch_comment','journal_entry_number');
    
    protected $revisionFormattedFieldNames = array(
        'status' => 'Status',
        'type'  => 'Type',
        'date' => 'Date Due',
        'amount' => 'Amount',
        'cheque_dispatch' => 'Cheque Dispatch',
        'cheque_dispatch_comment' => 'Cheque Comment',
        'journal_entry_number' => 'Journal Entry Number'
    );
    
    public $saveCreateRevision = true;
    public $softDelete = true;
    public $revisionClassName =  "Accounts Payable Payment";
    public $revisionPrimaryIdentifier = "id";
    
    /* Elastic Search */
    
    //Indexing Enabled
    public $esEnabled = true;
    //Context for Indexing
    public $esContext = "acpayable";
    //Info Context
    public $esInfoContext = "payment";
    public $esRemove = ['cheque_dispatch_comment','acp_id'];

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

    public function getChequeDispatchRevisionableAttribute($val)
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

    public function getTypeRevisionableAttribute($val)
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

    public function getStatusRevisionableAttribute($val)
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

    public function getChequeDispatchEsAttribute($val)
    {
        return $this->getChequeDispatchRevisionableAttribute($val);
    }

    public function getTypeEsAttribute($val)
    {
        return $this->getTypeRevisionableAttribute($val);
    }

    public function getStatusEsAttribute($val)
    {
        return $this->getStatusRevisionableAttribute($val);
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
    
    /*
     * Relationships
     */

    public function ac()
    {
        return $this->belongTo('SwiftACPRequest','acp_id');
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
        return "fa-moneyr";
    }
    
    /*
     * Query
     */

    public function sumTotalAmountPaid($acp_id)
    {
        return self::where('acp_id','=',$acp_id)
            ->groupBy('acp_id')
            ->whereNull('deleted_at')
            ->sum('amount')
            ->get();
    }
    
}
