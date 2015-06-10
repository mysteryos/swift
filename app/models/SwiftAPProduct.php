<?php
/*
 * Name: A&P Request - Product
 * Description:
 */

class SwiftAPProduct extends Eloquent {
    use Illuminate\Database\Eloquent\SoftDeletingTrait;
    use \Venturecraft\Revisionable\RevisionableTrait; 
    use \Swift\ElasticSearchEventTrait;
    
    protected $table = "swift_ap_product";
    
    protected $fillable = array("aprequest_id","jde_itm","quantity","price","reason_code","reason_others");
    
    protected $guarded = array('id');
    
    protected $appends = array('name','reason_text');
    
    public $timestamps = true;
    
    protected $dates = ['deleted_at'];
    
    /* Revisionable */
    
    protected $revisionEnabled = true;
    
    protected $keepRevisionOf = array(
        'jde_itm','quantity','reason_code','reason_others'
    );
    
    protected $revisionFormattedFieldNames = array(
        'document_file_name' => 'Document Name',
        'jde_itm' => 'Product JDE Id',
        'quantity' => 'Product Quantity',
        'reason_code' => 'Reason Code',
        'reason_others' => 'Reason(specify)',
        'id'        =>  'id'
    );    
    
    public static $revisionName = "A&P Product";
    
    public $revisionClassName = "A&P Product";
    public $revisionPrimaryIdentifier = "id";
    public $keepCreateRevision = true;
    public $softDelete = true;
    public $revisionDisplayId = true;
    
    
    /*
     * Constants : Reason Codes
     */
    
    const RC_EVENT = 1;
    const RC_TESTING = 2;
    const RC_SPONSOR = 3;
    const RC_TRAINING = 4;
    const RC_CONTRIBUTION = 5;
    const RC_LISTING = 6;
    const RC_COMPLAINT = 7;
    
    public static $reason = array(self::RC_CONTRIBUTION => 'Contribution',
                                  self::RC_COMPLAINT => 'Customer Complaint',
                                  self::RC_EVENT => 'Event',
                                  self::RC_LISTING => 'Listing',
                                  self::RC_SPONSOR => 'Sponsorship',
                                  self::RC_TESTING => 'Testing/Tasting',        
                                  self::RC_TRAINING => 'Training');
    
    /*
     * Revisionable Accessors
     */
    
    public function getReasonCodeRevisionAttribute($val)
    {
        if(key_exists($val,self::$reason))
        {
            return self::$reason[$val];
        }
        else
        {
            return "";
        }           
    }
    
    /*
     * Elastic Search Indexing
     */
    
    //Indexing Enabled
    public $esEnabled = true;
    //Context for Indexing
    public $esContext = "aprequest";
    public $esInfoContext = "product";
    public $esRemove = ['aprequest_id','reason_text'];
    
    public function getReasonCodeEsAttribute($val)
    {
        return $this->getReasonCodeRevisionAttribute($val);         
    }
    
    public function esGetParent()
    {
        return $this->aprequest;
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
        if($this->jde_itm !== "" && count($this->jdeproduct) !== 0)
        {
            return trim($this->jdeproduct->DSC1);
        }
        
        return "";
    }

    public function getReasonTextAttribute()
    {
        if($this->reason_code !== null)
        {
            if(key_exists($this->reason_code,self::$reason))
            {
                return self::$reason[$this->reason_code];
            }
        }
        
        return "N/A";
    }
    
    public function totalprice()
    {
        if($this->price > 0 && $this->quantity >0)
        {
            return round($this->price*$this->quantity,2);
        }
        return 0;
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
    
    public function approvalcatman()
    {
        return $this->morphOne('SwiftApproval','approvable')->with('comment')->where('type','=',SwiftApproval::APR_CATMAN);
    }
    
    public function approvalexec()
    {
        return $this->morphOne('SwiftApproval','approvable')->with('comment')->where('type','=',SwiftApproval::APR_EXEC);
    }    
}
