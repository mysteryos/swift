<?php
/*
 * Name: Swift A&P Request
 * Description:
 */

class SwiftAPRequest extends Eloquent {
    use Illuminate\Database\Eloquent\SoftDeletingTrait;
    use \Venturecraft\Revisionable\RevisionableTrait;
    use \Swift\ElasticSearchEventTrait;
    
    public $readableName = "A&P Request";
    
    protected $table = "swift_ap_request";
    
    protected $fillable = array("requester_user_id","customer_code","name","description","feedback_star","feedback_text");
    
    protected $guarded = array('id');
    
    protected $appends = array("customer_name");
    
    public $timestamps = true;
    
    protected $dates = ['deleted_at'];
    
    /* Revisionable */
    
    protected $revisionEnabled = true;
    
    protected $keepRevisionOf = array(
        'customer_code','name','description','feedback_star', 'feedback_text'
    );
    
    protected $revisionFormattedFieldNames = array(
        'customer_code' => 'Customer Code',
        'name' => 'Name',
        'description' => 'Description',
        'feedback_star' => 'Feedback Star',
        'feedback_text' => 'Feedback Text',
    );
    
    public $revisionClassName = "A&P Request";
    public $revisionPrimaryIdentifier = "name";
    public $keepCreateRevision = true;
    public $softDelete = true;
    
    /*
     * Elastic Search Indexing
     */
    
    //Indexing Enabled
    public $esEnabled = true;
    //Context for Indexing
    public $esContext = "aprequest";
    public $esInfoContext = "aprequest";
    //Main Document
    public $esMain = true;
    //Excludes
    public $esRemove = ['feedback_star','feedback_text'];
    
    /*
     * Event Observers
     */
    
    public static function boot() {
        parent:: boot();
        
        static::bootElasticSearchEvent();
        
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
        return "fa-gift";
    }
    
    //Pusher
    public function channelName()
    {
        return "apr_".$this->id;
    }
    
    /*
     * Accessor
     */
    
    public function getCustomerNameAttribute()
    {
        if($this->customer_code !== "" && count($this->customer) !== 0)
        {
            return trim($this->customer->ALPH);
        }
        
        return "";
    }
    
    /*
     * Relationships
     */
    
    public function customer()
    {
        return $this->belongsTo('JdeCustomer','customer_code','AN8');
    }
    
    public function product()
    {
        return $this->hasMany('SwiftAPProduct','aprequest_id');
    }
    
    public function requester()
    {
        return $this->belongsTo('users','requester_user_id');
    }
    
    /*
     * Morphic
     */
    
    public function comments()
    {
        return $this->morphMany('SwiftComment', 'commentable');
    }
    
    public function workflow()
    {
        return $this->morphOne('SwiftWorkflowActivity', 'workflowable');
    }
    
    public function order()
    {
        return $this->morphMany('SwiftErpOrder','orderable');
    }
    
    public function document()
    {
        return $this->morphMany('SwiftDocument','document');
    }
    
    public function flag()
    {
        return $this->morphMany('SwiftFlag','flaggable');
    }
    
    public function approval()
    {
        return $this->morphMany('SwiftApproval','approvable');
    }
    
    public function delivery()
    {
        return $this->morphMany('SwiftDelivery','deliverable');
    }
    
    public function recent()
    {
        return $this->morphMany('SwiftRecent','recentable');
    }
    
    public function notification()
    {
        return $this->morphMany('SwiftNotification','notifiable');
    }
    
    public function story()
    {
        return $this->morphMany('SwiftStory','storyfiable');
    }
    
    /*
     * Helper Function
     */
    
    public static function getById($id)
    {
        return self::with('customer','product','product.jdeproduct','product.approval','product.approvalcatman','product.approvalexec','delivery','approval','order','document')->find($id);
    }

    /*
     * Query
     */
    
    public static function getInProgress($limit=0,$important = false)
    {
        $query = self::query();
        if($limit > 0)
        {
            $query->take($limit);
        }
        
        return $query->orderBy('updated_at','desc')
                            ->with('workflow','workflow.nodes')->whereHas('workflow',function($q){
                                return $q->inprogress()
                                        ->whereHas('nodes',function($q){
                                             return $q->where('user_id','=',0)->whereHas('permission',function($q){
                                                 return $q->where('permission_type','=',SwiftNodePermission::RESPONSIBLE,'AND')
                                                        ->whereIn('permission_name',(array)array_keys(Sentry::getUser()->getMergedPermissions()));
                                            },'=',0);
                                        }); 
                            })->whereHas('flag',function($q){
                                return $q->where('type','=',SwiftFlag::IMPORTANT,'AND')->where('active','=',SwiftFlag::ACTIVE);
                            },($important === true ? ">" : "="),0)->get();        
    }
    
    public static function getInProgressResponsible($limit=0,$important=false)
    {
        $query = self::query();
        if($limit > 0)
        {
            $query->take($limit);
        }
        
        return $query->orderBy('updated_at','desc')
                            ->with('workflow','workflow.nodes')->whereHas('workflow',function($q){
                                return $q->inprogress()
                                        ->whereHas('nodes',function($q){
                                             return $q->where('user_id','=',0)->whereHas('permission',function($q){
                                                 return $q->where('permission_type','=',SwiftNodePermission::RESPONSIBLE,'AND')
                                                        ->whereIn('permission_name',(array)array_keys(Sentry::getUser()->getMergedPermissions()));
                                            });
                                        }); 
                            })->whereHas('flag',function($q){
                                return $q->where('type','=',SwiftFlag::IMPORTANT,'AND')->where('active','=',SwiftFlag::ACTIVE);
                            },($important === true ? ">" : "="),0)->get();
    }
    
    public static function getInProgressCount()
    {
        return self::orderBy('updated_at','desc')
                            ->with('workflow','workflow.nodes')->whereHas('workflow',function($q){
                                return $q->inprogress()
                                        ->whereHas('nodes',function($q){
                                             return $q->where('user_id','=',0)->whereHas('permission',function($q){
                                                 return $q->where('permission_type','=',SwiftNodePermission::RESPONSIBLE,'AND')
                                                        ->whereIn('permission_name',(array)array_keys(Sentry::getUser()->getMergedPermissions()));
                                            },'=',0);
                                        }); 
                            })->whereHas('flag',function($q){
                                return $q->where('type','=',SwiftFlag::IMPORTANT,'AND')->where('active','=',SwiftFlag::ACTIVE);
                            },'=',0)->count();
    }
    
    public static function getMyPending($limit=0)
    {
        
        $query = self::query();
        if($limit > 0)
        {
            $query->take($limit);
        }        
        
        return $query->orderBy('updated_at','desc')
                    ->with('workflow','workflow.nodes')
                    ->where('requester_user_id','=',Sentry::getUser()->id)
                    ->whereHas('workflow',function($q){
                        return $q->inprogress();
                    })
                    ->get();
    }
    
    public static function getMyCompleted($limit=0)
    {
        $query = self::query();
        if($limit > 0)
        {
            $query->take($limit);
        }
        
        return $query->orderBy('updated_at','desc')
                    ->with('workflow','workflow.nodes')
                    ->where('requester_user_id','=',Sentry::getUser()->id)
                    ->whereHas('workflow',function($q){
                        return $q->complete();
                    })
                    ->get();
    }

}
