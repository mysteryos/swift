<?php

class SwiftErpOrder extends Eloquent {
    use Illuminate\Database\Eloquent\SoftDeletingTrait;
    use \Venturecraft\Revisionable\RevisionableTrait;
    use \Swift\ElasticSearchEventTrait;    
    
    protected $table = "swift_erp_order";
    
    protected $guarded = array('id');
    
    protected $fillable = array('ref','status','type','orderable_type','orderable_id');
    
    protected $dates = ['deleted_at'];
    
    public $timestamps = true;
    
    const FILLED = 1;
    const CANCELLED = 2;
    
    const TYPE_CASH = 1;
    const TYPE_CREDIT = 2;
    const TYPE_AP = 3;
    
    public static $status = array(
                                self::FILLED => 'Filled',
                                self::CANCELLED => 'Cancelled'
                            );
    
    public static $type = array(
                                self::TYPE_CASH => '3C - Cash',
                                self::TYPE_CREDIT => '4C - Credit',
                                self::TYPE_AP => '9C - A&P'
                            );
    
    
    /* Revisionable */
    
    protected $revisionEnabled = true;
    
    protected $keepRevisionOf = array(
        'ref','status','type'
    );
    
    protected $revisionFormattedFieldNames = array(
        'ref' => 'Jde Order Reference',
        'status' => 'Jde Order Status',
        'type'  => 'Jde Order Type'
    );    
    
    public $revisionClassName = "Erp Order";
    public $revisionPrimaryIdentifier = "ref";
    public $keepCreateRevision = true;
    public $softDelete = true;
    
    /*
     * Revision Accessors
     */
    
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
    
    /*
     * Elastic Search Indexing
     */
    
    //Indexing Enabled
    public $esEnabled = true;
    public $esInfoContext = "order";
    public $esExcludes = array('created_at','updated_at','deleted_at','status','orderable_type','orderable_id');
    
    /*
     * ElasticSearch Utility Id
     */
    
    public function esGetId()
    {
        return $this->orderable_id;
    }
    
    public function esGetContext()
    {
        switch($this->orderable_type)
        {
            case "SwiftAPRequest":
                return "aprequest";
                break;
            default:
                return false;
        }
    }
    
    public function getTypeEsAttribute($val)
    {
        return $this->getTypeRevisionAttribute($val);
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
     * PolyMorphic Relationships
     */
    
    public function orderable()
    {
        return $this->morphTo();
    }
    
    public function aprequest()
    {
        return $this->morphByMany('SwiftAPRequest','orderable');
    }
    
}
