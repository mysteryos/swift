<?php
/**
 * Description of SwiftSupplierCredit
 *
 * @author kpudaruth
 */

class SwiftSupplierCredit extends Eloquent
{
    use \Illuminate\Database\Eloquent\SoftDeletingTrait;
    use \Venturecraft\Revisionable\RevisionableTrait;
    
    protected $table = "swift_supplier_credit";
    
    protected $fillable = ['type','type_day','due_on'];
    
    protected $dates = ['deleted_at'];

    protected $appends = ['text-type','text-due-on'];

    const TYPE_DAY = 1;
    const TYPE_ENDOFMONTH = 2;
    const TYPE_NTH_DAY = 3;

    const DUE_ON_INVOICE = 1;
    const DUE_ON_DELIVERY = 2;

    public static $type_list = [
        self::TYPE_DAY => 'days',
        self::TYPE_ENDOFMONTH => 'month end',
        self::TYPE_NTH_DAY => 'nth day'
    ];

    public static $due_on_list = [
        self::DUE_ON_INVOICE => 'On Invoice',
        self::DUE_ON_DELIVERY => 'On Delivery'
    ];
    
    /* Revisionable */
    
    protected $revisionEnabled = true;
    
    protected $keepRevisionOf = array('type','type_day','due_on');
    
    protected $revisionFormattedFieldNames = array(
        'type' => 'Credit Type',
        'type_day' => 'Credit Type Days',
        'due_on' => 'Credit Due On'
    );
    
    public $saveCreateRevision = true;
    public $softDelete = true;
    public $revisionClassName =  "Supplier Credit Terms";
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

    public function getTextTypeAttribute()
    {
        if(array_key_exists($this->type,self::$type_list))
        {
            return self::$type_list[$this->type];
        }
        else
        {
            return "";
        }
    }

    public function getTextDueOnAttribute()
    {
        if(array_key_exists($this->due_on,self::$due_on_list))
        {
            return self::$due_on_list[$this->due_on];
        }
        else
        {
            return "";
        }
    }
    
    /*
     * Scope
     */
    
    /*
     * Relationships
     */

    public function supplier()
    {
        return $this->hasOne('JdeSupplier','supplier_code','Supplier_Code');
    }
    
    /*
     * Query
     */
    
}
