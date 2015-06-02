<?php
/**
 * Description of SwiftPickup
 *
 * @author kpudaruth
 */

class SwiftPickup extends Eloquent
{
    use \Illuminate\Database\Eloquent\SoftDeletingTrait;
    use \Venturecraft\Revisionable\RevisionableTrait;
    use \Swift\ElasticSearchEventTrait;

    protected $table = "swift_pickup";
    
    protected $fillable = ['pickup_date','driver_id','print_count','status'];

    protected $appends = ['driver_name','status_text'];
    
    protected $dates = ['deleted_at','pickup_date'];

    protected $with = ['driver'];

    /*
     * Revisionable
     */

    protected $revisionEnabled = true;

    protected $keepRevisionOf = array(
        'pickup_date','driver_id','print_count','status'
    );

    protected $revisionFormattedFieldNames = array(
        'pickup_date' => 'Pickup Date',
        'print_count' => 'Print Count',
        'driver_id' => 'Driver',
        'status' => 'Status'
    );

    public $revisionClassName = "Pickup";
    public $revisionPrimaryIdentifier = "id";
    public $keepCreateRevision = true;
    public $softDelete = true;

    /*
     * Constants
     */

    const COLLECTION = 1;
    const RECEPTION = 2;
    const DELIVERY = 3;
    const CANCELLED = -1;

    public static $status = [self::COLLECTION => 'Collection Sent',
                      self::DELIVERY => 'Delivery Completed',
                      self::RECEPTION => 'Received Goods',
                      self::CANCELLED => 'Cancelled'];

    public static $pr_status = [self::COLLECTION => 'Collection Sent',
                      self::DELIVERY => 'Delivery Completed',
                      self::CANCELLED => 'Cancelled'];

    /*
     * Elastic Search Indexing
     */

    //Indexing Enabled
    public $esEnabled = true;
    public $esInfoContext = "pickup";
    public $esRemove = ['print_count','pickable_type','pickable_id','status','driver_id','comment'];

    public function esGetContext() {
        return array_search($this->pickable_type,\Config::get('context'));
    }

    public function esGetParent()
    {
        return $this->pickable;
    }

    /*
     * Event Observers
     */

    public static function boot() {
        parent::boot();

        static::bootElasticSearchEvent();

        static::bootRevisionable();
    } 

    /*
     * Accessors
     */

    public function getStatusTextAttribute()
    {
        if($this->status > 0)
        {
            return self::$status[$this->status];
        }

        return "N/A";
    }

    public function getStatusRevisionAttribute($val)
    {
        return $this->status_text;
    }

    public function getDriverIdRevisionAttribute($val)
    {
        if(count($this->driver))
        {
            return $this->driver->name;
        }

        return "N/A";
    }

    public function getDriverNameAttribute()
    {
        if(count($this->driver))
        {
            return $this->driver->name;
        }

        return "N/A";
    }
    
    /*
     * Scope
     */
    
    /*
     * Relationships
     */

    public function driver()
    {
        return $this->belongsTo('SwiftDriver','driver_id')->withTrashed();
    }

    public function pickable()
    {
        return $this->morphTo();
    }
    
    /*
     * Query
     */
    
}
