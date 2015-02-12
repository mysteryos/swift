<?php
/**
 * Description of SwiftSalesCommissionScheme
 *
 * @author kpudaruth
 */
class SwiftSalesCommissionScheme extends Eloquent {

    use Illuminate\Database\Eloquent\SoftDeletingTrait;
    use \Venturecraft\Revisionable\RevisionableTrait;
    
    protected $table = "swift_com_scheme";
    
    protected $fillable = ['name','notes','type'];
    
    protected $dates = ['deleted_at'];
    
    /* Revisionable */
    
    protected $revisionEnabled = true;
    protected $keepRevisionOf = array(
        'name', 'notes', 'type'
    );
    
    protected $revisionFormattedFieldNames = array(
        'name' => 'Name',
        'notes' => 'Notes',
        'type'  =>  'Type'
    );
    
    public $revisionClassName = "Scheme";
    public $revisionPrimaryIdentifier = "id";
    public $keepCreateRevision = true;
    public $softDelete = true;
    
    const KEYACCOUNT_FLAT_SALES = 1;
    const KEYACCOUNT_DYNAMIC_PRODUCTCATEGORY = 2;
    
    public static $type = [
                        self::KEYACCOUNT_FLAT_SALES => 'Key-account commision from monhtly sales figures',
                        self::KEYACCOUNT_DYNAMIC_PRODUCTCATEGORY => 'Key-account commission from monhtly sales of product by category'
                    ];
    
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
    
    public function getIsActiveAttribute()
    {
        foreach($this->rate as $r)
        {
            if($r->isActive)
            {
                return true;
            }
        }
        return false;
    }
    
    public function isActive(\Carbon\Carbon $date)
    {
        foreach($this->rate as $r)
        {
            if($r->isActive($date))
            {
                return true;
            }
        }
        return false;        
    }
    
    public function isActiveBetween(\Carbon\Carbon $date_start, \Carbon\Carbon $date_end)
    {
        foreach($this->rate as $r)
        {
            if($r->isActiveBetween($date_start,$date_end))
            {
                return true;
            }
        }
        return false;         
    }
    
    /*
     * Utility
     */
    
    public function getClassName()
    {
        return $this->revisionClassName;
    }
    
    public function getReadableName($html = false)
    {
        return $this->name." (Id:".$this->id.")";
    }
    
    public function getIcon()
    {
        return "fa-list";
    }
    
    /*
     * Pusher Channel name
     */
    public function channelName()
    {
        return "sales_commission_product_category".$this->id;
    }      
    
    /*
     * Relationships
     */
    
    public function rate()
    {
        return $this->hasMany('SwiftSalesCommissionSchemeRate','scheme_id');
    }
    
    public function product()
    {
        return $this->hasMany('SwiftSalesCommissionSchemeProduct','scheme_id');
    }
    
    public function comments()
    {
        return $this->morphMany('SwiftComment', 'commentable');
    }
    
    public function salesman()
    {
        return $this->belongsToMany('SwiftSalesman','swift_com_scheme_salesman','scheme_id','salesman_id');
    }
    
    /*
     * Utility
     */
    
    public static function getById($id,$trashed=false)
    {
        $query = self::query();
        if($trashed)
        {
            $query->withTrashed();
        }
        
        return $query->with('rate','product','salesman','salesman.user')->find($id);
    }
    
    
}
