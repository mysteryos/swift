<?php
/*
 * Name: Swift Account Payables Order
 * Description:
 */

Use \Swift\Core\Collection;

class SwiftAPOrder extends Eloquent {
    use Illuminate\Database\Eloquent\SoftDeletingTrait;
    use \Venturecraft\Revisionable\RevisionableTrait;
    use \Swift\ElasticSearchEventTrait;    
    
    protected $table = "swift_ap_order";
    
    protected $fillable = array("aprequest_id","ref","status");
    
    protected $guarded = array('id');
    
    public $timestamps = true;
    
    protected $dates = ['deleted_at'];
    
    /* Revisionable */
    
    protected $revisionEnabled = true;
    
    protected $keepRevisionOf = array(
        'ref','status'
    );
    
    protected $revisionFormattedFieldNames = array(
        'ref' => 'Order Reference',
        'status' => 'Order Status',
    );    
    
    public $revisionClassName = "A&P Order";
    public $revisionPrimaryIdentifier = "ref";    
    public $keepCreateRevision = true;
    public $softDelete = true;
    
    /*
     * Elastic Search Indexing
     */
    
    //Indexing Enabled
    public $esEnabled = true;
    //Context for Indexing
    public $esContext = "aprequest";
    public $esInfoContext = "order";
    public $esRemove = ['created_at','deleted_at','updated_at','aprequest_id'];
    
    public function esGetParent()
    {
        return $this->aprequest;
    }
    
    /*
     * Constants
     */
    
    const FILLED = 1;
    const CANCELLED = 2;
    
    /*
     * Attributes
     */
    
    public static $status = array(self::FILLED=>'Filled on JDE',
                                    self::CANCELLED=>'Cancelled on JDE');    
    
    /*
     * Revisionable Functions
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
    
    
    /*
     * Event Observers
     */
    
    public static function boot() {
        parent:: boot();
        
        static::bootElasticSearchEvent();
        
        static::bootRevisionable();
    }    
        
    
    /*
     * Relationships
     */
    public function aprequest()
    {
        return $this->belongTo('SwiftAPRequest','aprequest_id');
    }
}
