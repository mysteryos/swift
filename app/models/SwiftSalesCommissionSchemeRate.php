<?php
/**
 * Description of SwiftSalesCommissionSchemeProductRate
 *
 * @author kpudaruth
 */
class SwiftSalesCommissionSchemeRate extends Eloquent {
    
    use Illuminate\Database\Eloquent\SoftDeletingTrait;
    use \Venturecraft\Revisionable\RevisionableTrait;    
    
    protected $table = "swift_com_scheme_rate";
    
    protected $fillable = ['effective_date_start','effective_date_end','rate','status'];
    
    protected $dates = ['deleted_at','effective_date_start','effective_date_end'];
    
    protected $appends = ['isActive'];
    
    /* Revisionable */
    
    protected $revisionEnabled = true;
    protected $keepRevisionOf = array(
        'effective_date_start','effective_date_end','rate','active'
    );
    
    protected $revisionFormattedFieldNames = array(
        'effective_date_start'  => 'Effective Start Date',
        'effective_date_end'    => 'Effective End Date',
        'rate'                  => 'Rate',
        'status'                => 'Status',
    );
    
    public $revisionClassName = "Commision Rate";
    public $revisionPrimaryIdentifier = "id";
    public $keepCreateRevision = true;
    public $softDelete = true;
    
    const ACTIVE = 1;
    const INACTIVE = 0;
    
    public static $status = [self::ACTIVE => 'Active',self::INACTIVE => 'Inactive'];
    
    public static function boot() {
        parent:: boot();
        
        static::bootRevisionable();
    }
    
    public function scopeActive($query)
    {
        return $query->where('status','=',self::ACTIVE);
    }
    
    public function scopeInactive($query)
    {
        return $query->where('status','=',self::INACTIVE);
    }
    
    public function getIsActiveAttribute()
    {
        if($this->effective_date_start !== null && $this->effective_date_end !== null && $this->status === self::ACTIVE)
        {
            if(Carbon::now()->between($this->effective_date_start,$this->effective_date_end))
            {
                return true;
            }
        }
        return false;
    }
    
    public function isActive(\Carbon\Carbon $date)
    {
        if($this->effective_date_start !== null && $this->effective_date_end !== null && $this->status === self::ACTIVE)
        {
            if($date->between($this->effective_date_start,$this->effective_date_end))
            {
                return true;
            }
        }
        return false;        
    }
    
    public function isActiveBetween(\Carbon\Carbon $date_start, \Carbon\Carbon $date_end)
    {
        if($this->effective_date_start !== null && $this->effective_date_end !== null && $this->status === self::ACTIVE)
        {
            if($date_start <= $this->effective_date_end && $date_end >= $this->effective_date_start)
            {
                return true;
            }
        }
        return false;        
    }    
    
    /*
     * Relationships
     */
    
    public function category()
    {
        return $this->belongsTo('SwiftSalesCommissionScheme','scheme_id');
    }

}
