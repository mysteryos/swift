<?php
/**
 * Description of SupplierPaymentTerm
 *
 * @author kpudaruth
 */

class SupplierPaymentTerm extends Eloquent
{
    use \Illuminate\Database\Eloquent\SoftDeletingTrait;
    use \Venturecraft\Revisionable\RevisionableTrait;
    
    protected $table = "scott_swift.supplier_payment_term";
    
    protected $fillable = ['supplier_code','type','day','type_day','when','how'];
    
    protected $dates = ['deleted_at'];

    protected $appends = ['term_name'];

    protected $with = ['term'];

    const CHEQUE = 1;
    const BANK_TRANSFER = 2;
    const DIRECT_DEBIT = 3;
    const CASH = 4;

    public static $typeList = [
        self::CHEQUE => 'Cheque',
        self::BANK_TRANSFER => 'Bank Transfer',
        self::DIRECT_DEBIT => 'Direct Debit',
        self::CASH => 'Cash'
    ];
    
    /* Revisionable */
    
    protected $revisionEnabled = true;
    
    protected $keepRevisionOf = ['type','day','type_day','when','how'];
    
    protected $revisionFormattedFieldNames = [  "type" => "Type",
                                                "day" => "Day",
                                                "type_day" => "Type Day",
                                                "when" => "When",
                                                "how" => "How"];
    
    public $saveCreateRevision = true;
    public $softDelete = true;
    public $revisionClassName =  "Payment Term";
    public $revisionPrimaryIdentifier = "id";
    
    /*
     * Event Observers
     */
    
    public static function boot() {
        parent:: boot();
        
        static::bootRevisionable();
    }    
    
    /*
     * Accessors
     */

    public function getTermNameAttribute()
    {
        if($this->term)
        {
            return $this->term->name;
        }

        return "";
    }
    
    /*
     * Scope
     */
    
    /*
     * Relationships
     */

    public function supplier()
    {
        return $this->belongsTo('JdeSupplierMaster','supplier_code','Supplier_Code');
    }

    public function term()
    {
        return $this->hasOne('PaymentTerm','id','term_id');
    }
    
    /*
     * Query
     */
    
}
