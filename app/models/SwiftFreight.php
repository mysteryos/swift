<?php
/* 
 * Name: Swift Freight
 */


class SwiftFreight extends Eloquent{
    use Illuminate\Database\Eloquent\SoftDeletingTrait;
    use \Venturecraft\Revisionable\RevisionableTrait;
    use \Swift\ElasticSearchEventTrait;
    
    protected $table = "swift_freight";
    
    protected $guarded = array('id');
    
    protected $fillable = array('order_id','freight_company_id','freight_type','bol_no','vessel_name','vessel_voyage','incoterms','freight_etd','freight_eta','shipment_type','volume');
    
    public $timestamps = true;
    
    protected $touches = array('order');
    
    protected $dates = ['deleted_at','freight_etd','freight_eta'];
    
    public static $incoterms = array(self::INCOTERM_FOB=>'FOB',
                                    self::INCOTERM_EXW=>'EXW',
                                    self::INCOTERM_FCA=>'FCA',
                                    self::INCOTERM_FAS=>'FAS',
                                    self::INCOTERM_CFR=>'CFR',
                                    self::INCOTERM_CIF=>'CIF',
                                    self::INCOTERM_CPT=>'CPT',
                                    self::INCOTERM_CIP=>'CIP',
                                    self::INCOTERM_DAT=>'DAT',
                                    self::INCOTERM_DAP=>'DAP',
                                    self::INCOTERM_DDU=>'DDU',
                                    self::INCOTERM_DDP=>'DDP');
    
    public static $type = array(self::TYPE_SEA=>'Sea',self::TYPE_AIR=>'Air',self::TYPE_LAND=>'Land');
    
    
    
    //Freight Type constants
    const TYPE_SEA = 1;
    const TYPE_AIR = 2;
    const TYPE_LAND = 3;
    
    //Incoterms Constants
    const INCOTERM_FOB = 1;
    const INCOTERM_EXW = 2;
    const INCOTERM_FCA = 3;
    const INCOTERM_FAS = 4;
    const INCOTERM_CFR = 5;
    const INCOTERM_CIF = 6;
    const INCOTERM_CPT = 7;
    const INCOTERM_CIP = 8;
    const INCOTERM_DAT = 9;
    const INCOTERM_DAP = 10;
    const INCOTERM_DDP = 11;
    const INCOTERM_DDU = 12;
    
    
    /* Revisionable Attributes */
    
    protected $revisionEnabled = true;
    
    protected $keepRevisionOf = array(
        'freight_company_id','freight_type','bol_no', 'vessel_no', 'vessel_name', 'incoterms', 'freight_etd', 'freight_eta'
    );
    
    protected $revisionFormattedFieldNames = array(
        'freight_type' => 'Freight Type',
        'bol_no' => 'Bill of Lading Number',
        'vessel_no' => 'Vessel Number',
        'vessel_name' => 'Vessel Name',
        'incoterms' => 'Incoterms',
        'freight_etd' => 'Freight ETD',
        'freight_eta' => 'Freight ETA',
        'freight_company' => 'Freight Company',
    );
    
    public $keepCreateRevision = true;
    public $softDelete = true;
    public $revisionClassName = "Freight";
    public $revisionPrimaryIdentifier = "id";
    
    /*
     * Elastic Search Indexing
     */
    
    //Indexing Enabled
    public $esEnabled = true;
    //Context for Indexing
    public $esContext = "order-tracking";    
    
    /*
     * ElasticSearch Utility Id
     */
    
    public function esGetId()
    {
        return $this->order_id;
    }
    
    public function esGetInfoContext()
    {
        return "freight";
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
     * Revision - Accessors
     */
    
    public function getIncotermsRevisionAttribute($val)
    {
        if(key_exists($val,self::$incoterms))
        {
            return self::$incoterms[$val];
        }
        else
        {
            return "";
        }
    }
    
    public function getFreightTypeRevisionAttribute($val)
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
    
    public function getFreightEtaRevisionAttribute($val)
    {
        if($val != "")
        {
            return Carbon::parse($val)->toDateString();
        }
        return "";
    }
    
    public function getFreightEtdRevisionAttribute($val)
    {
        if($val != "")
        {
            return Carbon::parse($val)->toDateString();
        }
        return "";
    }
    
    public function getFreightCompanyIdRevisionAttribute($val)
    {
        if($val != "")
        {
            return SwiftFreightCompany::find($val)->first()->name;
        }
        return "";
    }
    
    /*
     * Accessors
     */
    
    public function getIncotermstextAttribute()
    {
        if(key_exists($this->incoterms,self::$incoterms))
        {
            return self::$incoterms[$val];
        }
        else
        {
            return "(unknown)";
        }        
    }
    
    /*
     * Relationships
     */
    
    public function order()
    {
        return $this->belongsTo('SwiftOrder','order_id');
    }
    
    public function company()
    {
        return $this->belongsTo('SwiftFreightCompany','freight_company_id');
    }
    
}