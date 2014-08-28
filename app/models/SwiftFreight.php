<?php
/* 
 * Name: Swift Freight
 */


class SwiftFreight extends Eloquent{
    use Illuminate\Database\Eloquent\SoftDeletingTrait;
    use \Venturecraft\Revisionable\RevisionableTrait;
    
    protected $table = "swift_freight";
    
    protected $guarded = array('id');
    
    protected $fillable = array('order_id','freight_company_id','freight_type','bol_no','vessel_no','incoterms','freight_etd','freight_eta');
    
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
                                    self::INCOTERM_DAP=>'DAP');
    
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


    
    
    /* Revisionable Attributes */
    
    protected $revisionEnabled = true;
    
    protected $keepRevisionOf = array(
        'freight_company_id','freight_type','bol_no', 'vessel_no', 'incoterms', 'freight_etd', 'freight_eta'
    );
    
    protected $revisionFormattedFieldNames = array(
        'freight_type' => 'Freight Type',
        'bol_no' => 'Bill of Lading Number',
        'vessel_no' => 'Vessel Number',
        'incoterms' => 'Incoterms',
        'freight_etd' => 'Freight ETD',
        'freight_eta' => 'Freight ETA',
    );
    
    protected $keepCreateRevision = true;   
    
    protected $revisionClassName = "Freight";
    
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