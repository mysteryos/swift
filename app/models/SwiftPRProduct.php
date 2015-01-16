<?php
/*
 * Name: Swift A&P Product
 * Description:
 */

class SwiftPRProduct extends Eloquent {
    use Illuminate\Database\Eloquent\SoftDeletingTrait;
    use \Venturecraft\Revisionable\RevisionableTrait; 
    use \Swift\ElasticSearchEventTrait;
    
    protected $table = "swift_pr_product";
    
    protected $fillable = array("pr_id","jde_itm","qty_client","qty_pickup","qty_store",
                                "qty_triage_picking","qty_triage_disposal","invoice_id","invoice_recognition","price","reason_code","reason_others","pickup");
    
    protected $attributes = array('pickup'=>self::PICKUP);
    
    protected $guarded = array('id');
    
    protected $appends = array('name','reasontext');
    
    public $timestamps = true;
    
    protected $dates = ['deleted_at'];
    
    /* Revisionable */
    
    protected $revisionEnabled = true;
    
    protected $keepRevisionOf = array(
        'jde_itm','quantity','reason_code','reason_others'
    );
    
    protected $revisionFormattedFieldNames = array(
        'jde_itm' => 'JDE Id',
        'qty_client' => 'Quantity Client',
        'qty_pickup' => 'Quantity Pickup',
        'qty_store' => 'Quantity Store',
        'qty_triage_picking' => 'Quantity Picking',
        'qty_triage_disposal' => 'Quantity Disposal',
        'invoice_id' => 'Invoice Number',
        'price' => 'Price',
        'reason_code' => 'Reason Code',
        'reason_others' => 'Reason(specify)',
        'pickup' => "Pickup",
    );    
    
    public static $revisionName = "PR Product";
    
    public $revisionClassName = "PR Product";
    public $revisionPrimaryIdentifier = "name";
    public $keepCreateRevision = true;
    public $softDelete = true;
    public $revisionDisplayId = true;
    
    /*
     * Constants : Reason Codes
     */
    
    const RC_CLIENT_BROKEN = 1;
    const RC_CLIENT_CANCELLED = 2;
    const RC_CLIENT_CONSIGNMENT = 3;
    const RC_CLIENT_DAMAGED = 4;
    const RC_CLIENT_DELISTED = 5;
    const RC_CLIENT_EXPIRED = 6;
    const RC_CLIENT_INCORRECTORDER = 7;
    const RC_CLIENT_MISSING = 8;
    const RC_CLIENT_SPOILED = 9;
    const RC_SCOTT_BROKEN = 10;
    const RC_SCOTT_BROKENMERCHANDISER = 11;
    const RC_SCOTT_CODEBAR = 12;
    const RC_SCOTT_DAMAGED = 13;
    const RC_SCOTT_DISCOUNTINCORRECT = 14;
    const RC_SCOTT_PRICEINCORRECT = 15;
    const RC_SCOTT_PICKING = 16;
    const RC_SCOTT_SPOILED = 17;
    const RC_SCOTT_VALIDATION = 18;
    
    /*
     * Constants: Pickup
     */
    
    const PICKUP = 1;
    const NO_PICKUP = 0;
    
    //Reason Codes
    
    public static $reason_client = array(self::RC_CLIENT_BROKEN => 'Broken',
                                        self::RC_CLIENT_CANCELLED => 'Cancelled Order',
                                        self::RC_CLIENT_CONSIGNMENT => 'Consignment',
                                        self::RC_CLIENT_DAMAGED => 'Damaged',
                                        self::RC_CLIENT_DELISTED => 'Delisted',
                                        self::RC_CLIENT_EXPIRED => 'Expired',
                                        self::RC_CLIENT_INCORRECTORDER => 'Incorrect Order',
                                        self::RC_CLIENT_MISSING => 'Missing in Sealed Cartons',
                                        self::RC_CLIENT_SPOILED => 'Spoiled');
        
    public static $reason_scott = array(self::RC_SCOTT_BROKEN => 'Broken',
                                        self::RC_SCOTT_BROKENMERCHANDISER => 'Broken By Merchandiser',
                                        self::RC_SCOTT_CODEBAR => 'Code Bar',
                                        self::RC_SCOTT_DAMAGED => 'Damaged',
                                        self::RC_SCOTT_DISCOUNTINCORRECT => 'Discount Incorrect',
                                        self::RC_SCOTT_PRICEINCORRECT => 'Price Incorrect',
                                        self::RC_SCOTT_PICKING => 'Picking Error',
                                        self::RC_SCOTT_SPOILED => 'Spoiled',
                                        self::RC_SCOTT_VALIDATION => 'Validation error');
                                  
    
    /*
     * Revisionable Accessors
     */
    
    public function getReasonCodeRevisionAttribute($val)
    {
        if(key_exists($val,self::$reason_client))
        {
            return self::$reason[$val]. "(At Client)";
        }
        if(key_exists($val,self::$reason_scott))
        {
               return self::$reason[$val]. "(At Scott)"; 
        }
        
        return "";        
    }
    
    /*
     * Elastic Search Indexing
     */
    
    //Indexing Enabled
    public $esEnabled = true;
    //Context for Indexing
    public $esContext = "aprequest";
    public $esInfoContext = "product";
    
    /*
     * ElasticSearch Utility Id
     */
    
    public function esGetId()
    {
        return $this->aprequest_id;
    }
    
    public function getReasonCodeEsAttribute($val)
    {
        return $this->getReasonCodeRevisionAttribute($val);         
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
     * Accessor
     */
    public function getNameAttribute()
    {
        if($this->ITM !== "" && count($this->jdeproduct) !== 0)
        {
            return trim($this->jdeproduct->DSC1);
        }
        
        return "";
    }
    
    public function getReasontextAttribute()
    {
        if(key_exists($val,self::$reason_client))
        {
            return $this->$reason_client[$val]. "(At Client)";
        }
        if(key_exists($val,self::$reason_scott))
        {
               return self::$reason_scott[$val]. "(At Scott)"; 
        }
        
        return "";         
    }    
    
    /*
     * Relationships
     */
    public function aprequest()
    {
        return $this->belongsTo('SwiftAPRequest','aprequest_id');
    }
    
    public function approval()
    {
        return $this->morphMany('SwiftApproval','approvable');        
    }
    
    public function jdeproduct()
    {
        return $this->belongsTo('JdeProduct','jde_itm','ITM');
    }
    
    public function approvalretailman()
    {
        return $this->morphOne('SwiftApproval','approvable')->with('comment')->where('type','=',SwiftApproval::PR_RETAILMAN);
    }
    
    
    /*
     * Utility
     */
    
    public function reasonScott()
    {
        return $this->reason_scott;
    }
    
    public function reasonClient()
    {
        return $this->reason_client;
    }
    
}
