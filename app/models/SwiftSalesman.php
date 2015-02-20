<?php
/**
 * Description of SwiftSalesman
 *
 * @author kpudaruth
 */
class SwiftSalesman extends Eloquent {
    
    use Illuminate\Database\Eloquent\SoftDeletingTrait;
    use \Venturecraft\Revisionable\RevisionableTrait;    

    protected $table = "swift_salesman";
    
    protected $fillable = array("user_id");
    
    protected $appends = ['name'];
    
    protected $with = array('user');
    
    protected $dates = ['deleted_at'];
    
    /* Revisionable */
    
    protected $revisionEnabled = true;
    protected $keepRevisionOf = array(
        'user_id'
    );
    
    protected $revisionFormattedFieldNames = array(
        'user_id' => 'Employee'
    );
    
    public $revisionClassName = "Salesman";
    public $revisionPrimaryIdentifier = "id";
    public $keepCreateRevision = true;
    public $softDelete = true;
    
    //Used to retrieve last user who worked on this main model
    public $revisionRelations = ['client','salesbudget'];
    
    public static function boot() {
        parent:: boot();
        
        static::bootRevisionable();
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
        return "fa-user";
    }
    
    /*
     * Pusher Channel name
     */
    public function channelName()
    {
        return "salesman_".$this->id;
    }    
    
    /*
     * Accessors
     */
    
    public function getNameAttribute()
    {
        return $this->user->first_name." ".$this->user->last_name;
    }
    
    public function getUserIdRevisionableAttribute($val)
    {
        $user = \Sentry::findUserById($val);
        return $user->first_name." ".$user->last_name;
    }
    
    /*
     * Relationships
     */
    
    public function user()
    {
        return $this->belongsTo('User','user_id');
    }
    
    public function client()
    {
        return $this->hasMany('SwiftSalesmanClient','salesman_id');
    }
    
    public function scheme()
    {
        return $this->belongsToMany('SwiftSalesCommissionScheme','swift_com_scheme_salesman','salesman_id','scheme_id');
    }
    
    public function salesbudget()
    {
        return $this->hasMany('SwiftSalesCommissionBudget','salesman_id');
    }
    
    public function department()
    {
        return $this->belongsTo('SwiftSalesmanDepartment','department_id');
    }
    
    public function comments()
    {
        return $this->morphMany('SwiftComment', 'commentable');
    }
    
    public function story()
    {
        return $this->morphMany('SwiftStory','storyfiable');
    }
    
    /*
     * Utility
     */
    
    public static function getById($id,$trashed=false)
    {
        if(!$trashed)
        {
            return self::with('client','salesbudget','scheme','department')->find($id);
        }
        else
        {
            return self::withTrashed()->with('client','salesbudget','scheme','department')->find($id);
        }
    }    

}
