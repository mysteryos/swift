<?php
/*
 * Name: Swift Product Returns - Main Model
 * Description: All product returns
 */

class SwiftPR extends Eloquent {
    use Illuminate\Database\Eloquent\SoftDeletingTrait;
    use \Venturecraft\Revisionable\RevisionableTrait; 
    use \Swift\ElasticSearchEventTrait;

    public $readableName = "Product Returns";

    protected $table = "scott_swift.swift_pr";
    
    protected $guarded = array('id');
    
    protected $appends = array('customer_name','owner_name','company_name','type_name','name');
    
    protected $fillable = array('description','customer_code','company_code','owner_user_id','paper_number','type');

    public static $company = ['269'=>'Scott & Co Ltd'];

    protected $dates = ['deleted_at'];

    protected $attributes = ['type'=>self::SALESMAN];

    protected $with = ['product','customer','owner'];

    /*
     * Constants
     */

    const SALESMAN = 1;
    const ON_DELIVERY = 2;
    const INVOICE_CANCELLED = 3;

    public static $type = [self::SALESMAN => 'Salesman',
                    self::ON_DELIVERY => 'On Delivery',
                    self::INVOICE_CANCELLED => 'Invoice Cancelled'
                    ];
    
    /* Revisionable */
    
    protected $revisionEnabled = true;
    
    protected $keepRevisionOf = array(
        'description','customer_code','company_code','paper_number'
    );
    
    protected $revisionFormattedFieldNames = array(
        'customer_code' => 'Customer Code',
        'description' => 'Description',
        'company_code' => 'Company Code',
        'paper_number' => 'RFRF Paper number'
    );
    
    public $revisionClassName = "Product Returns";
    public $revisionPrimaryIdentifier = "id";
    public $keepCreateRevision = true;
    public $softDelete = true;
    public $revisionRelations = ['product','order','pickup','approval','document','creditnote'];
    
    
    /*
     * Elastic Search Indexing
     */
    
    //Indexing Enabled
    public $esEnabled = true;
    //Context for Indexing
    public $esContext = "product-returns";
    public $esInfoContext = "product-returns";
    //Main Document
    public $esMain = true;
    public $esRemove = ['owner_user_id','type'];
    
    
    /*
     * ElasticSearch Utility functions
     */
    
    public function esGetId()
    {
        return $this->id;
    }
    
    /*
     * Event Observers
     */
    
    public static function boot() {
        parent:: boot();
        
        static::bootElasticSearchEvent();
        
        static::bootRevisionable();
        
        static::creating(function($model){
            $model->owner_user_id = \Sentry::getUser()->id;
        });
    }

    /*
     * Accessors
     */

    public function getNameAttribute()
    {
        return $this->type_name." - ".$this->customer_name;
    }

    public function getOwnerNameAttribute()
    {
        if($user = \Sentry::findUserById($this->owner_user_id))
        {
            return $user->first_name." ".$user->last_name;
        }

        return "";
    }

    public function getCustomerNameAttribute()
    {
        if($this->customer_code !== "" && count($this->customer) !== 0)
        {
            return trim($this->customer->ALPH);
        }

        return "";
    }

    public function getCompanyNameAttribute()
    {
        if($this->company_code !== "" && count($this->company) !== 0)
        {
            return trim($this->company->ALPH);
        }

        return "";
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
     * Utility
     */
    
    public function getClassName()
    {
        return $this->revisionClassName;
    }
    
    public function getReadableName($html = false)
    {
        return $this->customer_name." (Id:".$this->id.")";
    }
    
    public function getIcon()
    {
        return "fa-reply";
    }
    
    //Pusher
    public function channelName()
    {
        return "pr_".$this->id;
    }

    public function isOwner($user_id=false)
    {
        if($user_id===false)
        {
            $user_id = \Sentry::getUser()->id;
        }

        return $this->owner_user_id === $user_id;
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
        return $this->hasMany('SwiftPRProduct','pr_id');
    }

    public function productApproved()
    {
        return $this->product()->whereHas('approvalretailman',function($q){
            return $q->where('approved','=',\SwiftApproval::APPROVED);
        });
    }
    
    public function owner()
    {
        return $this->belongsTo('user','owner_user_id');
    }

    
    /*
     * Morphic
     */
    
    public function workflow()
    {
        return $this->morphOne('SwiftWorkflowActivity', 'workflowable');
    }
    
    public function order()
    {
        return $this->morphMany('SwiftErpOrder','orderable');
    }
    
    public function pickup()
    {
        return $this->morphMany('SwiftPickup','pickable');
    }

    public function creditnote()
    {
        return $this->morphMany('SwiftCreditNote','creditable');
    }
    
    public function document()
    {
        return $this->morphMany('SwiftPRDocument','document');
    }
    
    public function flag()
    {
        return $this->morphMany('SwiftFlag','flaggable');
    }
    
    public function approval()
    {
        return $this->morphMany('SwiftApproval','approvable');
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

    public function comments()
    {
        return $this->morphMany('SwiftComment','commentable');
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
                                return $q->where('status','=',SwiftWorkflowActivity::INPROGRESS,'AND')
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
                                return $q->where('status','=',SwiftWorkflowActivity::INPROGRESS,'AND')
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
                                return $q->where('status','=',SwiftWorkflowActivity::INPROGRESS,'AND')
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
                    ->where('owner_user_id','=',Sentry::getUser()->id)
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
                    ->where('owner_user_id','=',Sentry::getUser()->id)
                    ->whereHas('workflow',function($q){
                        return $q->complete();
                    })
                    ->get();
    }

    public static function getById($id)
    {
        return self::with(['product','document','approval','pickup','pickup.driver','creditnote','order','comments','workflow','owner','customer'])
                ->find($id);
    }
}

