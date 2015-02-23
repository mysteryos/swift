<?php

class SwiftDriver extends Eloquent {
    
    use \Illuminate\Database\Eloquent\SoftDeletingTrait;
    use \Venturecraft\Revisionable\RevisionableTrait;
    
    protected $table = "swift_driver";
    
    protected $fillable = ["name","status"];
    
    protected $appends = ['status_text'];
    
    const ACTIVE = 1;
    const INACTIVE = 0;
    
    public static $status = [self::ACTIVE => 'Active',self::INACTIVE => 'Inactive'];
    
    /* Revisionable */
    
    protected $revisionEnabled = true;
    protected $keepRevisionOf = array(
        'name','status'
    );
    
    protected $revisionFormattedFieldNames = array(
        'name' => 'Name',
        'status' => 'Status'
    );
    
    public $revisionClassName = "Driver";
    public $revisionPrimaryIdentifier = "name";
    public $keepCreateRevision = true;
    public $softDelete = true;
    
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
    
    public function getStatusTextAttribute()
    {
        if(key_exists($this->status,self::$status))
        {
            return self::$status[$this->status];
        }        
        
        return "";
    }
    
    
}