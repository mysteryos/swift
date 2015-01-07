<?php
/**
 * Name: SwiftReception
 *
 * @author kpudaruth
 */

class SwiftReception extends Eloquent {
    
    use Illuminate\Database\Eloquent\SoftDeletingTrait;
    use \Venturecraft\Revisionable\RevisionableTrait;
    use \Swift\ElasticSearchEventTrait;
    
    protected $table = "swift_reception";
    
    protected $guarded = "id";
    
    protected $fillable = array('order_id','reception_date','grn','reception_user');
    
    public $timestamps = true;
    
    protected $touches = array('order');
    
    protected $dates = ['deleted_at','reception_date'];
    
    /* Revisionable Attributes */
    
    protected $revisionEnabled = true;
    
    protected $keepRevisionOf = array(
        'reception_date','grn','reception_user'
    );
    
    protected $revisionFormattedFieldNames = array(
        'reception_date' => 'Reception Date',
        'grn' => 'GRN number',
        'reception_user' => 'Received By'
    );
    
    public $revisionClassName = "Reception";
    public $revisionPrimaryIdentifier = "id";
    public $keepCreateRevision = true;
    public $softDelete = true;
    
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
        return "shipment";
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
     * Mutator
     */
    
    public function setReceptionDateAttribute($value)
    {
        //Add missing seconds value
        $this->attributes['reception_date'] = ($value != "" ? Carbon::parse($value)->toDateTimeString(): "");
    }
        
    /*
     * Relationships
     */
    public function order()
    {
        return $this->belongsTo('SwiftOrder','order_id');
    }
    
}
