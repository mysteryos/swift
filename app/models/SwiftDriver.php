<?php
/**
 * Description of SwiftDriver
 *
 * @author kpudaruth
 */

class SwiftDriver extends Eloquent
{
    use \Illuminate\Database\Eloquent\SoftDeletingTrait;
    use \Venturecraft\Revisionable\RevisionableTrait;
    
    protected $table = "swift_driver";
    
    protected $fillable = ['name','vehicle_number','type'];
    
    protected $dates = ['deleted_at'];

    const SCOTT = 1;
    const TAXI = 2;

    public static $type = [
        self::SCOTT => 'Scott',
        self::TAXI => 'Taxi'
    ];

    protected $appends = ['type_name'];

    /* Revisionable */
    
    protected $revisionEnabled = true;
    
    protected $keepRevisionOf = array('name','vehicle_number','type');
    
    protected $revisionFormattedFieldNames = array(
        'name' => 'Name',
        'vehicle_number' => 'Vehicle Number',
        'type' => 'Type'
    );
    
    public $saveCreateRevision = true;
    public $softDelete = true;
    public $revisionClassName =  "Driver";
    public $revisionPrimaryIdentifier = "name";
    
    
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

    public function getTypeNameAttribute()
    {
        if(key_exists($this->type,self::$type))
        {
            return self::$type[$this->type];
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

    public function pickup()
    {
        return $this->hasMany('SwiftPickup','driver_id');
    }

    /*
     * Query
     */

    public static function getAll()
    {
        $all = self::orderBy("name","ASC")
                    ->orderBy('vehicle_number','ASC')
                    ->get();
        $result = [];
        foreach($all as $row)
        {
            $result[$row->id] = $row->type_name." ".$row->name." ".$row->vehicle_number;
        }

        return $result;
    }
}
