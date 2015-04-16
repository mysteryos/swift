<?php
/*
 * Name: Swift Order
 * Description: Table that contains all orders
 */

class SwiftOrder extends Eloquent {
    
    use \Illuminate\Database\Eloquent\SoftDeletingTrait;    
    use \Venturecraft\Revisionable\RevisionableTrait;
    use \Swift\ElasticSearchEventTrait;
    
    public $readableName = "Order Process";
    
    protected $table = "swift_order";
    
    protected $guarded = array('id');
    
    protected $fillable = array('name','description','business_unit','supplier_code');

    protected $appends = ['supplier_name'];
    
    public $timestamps = true;
    
    public $dates = ['deleted_at'];

    public $subscriptionable = true;
    
    public static $business_unit = array(self::SCOTT_CONSUMER=>'Scott Consumer',self::SCOTT_HEALTH=>'Scott Health',self::SEBNA=>'Sebna');
    
    const SCOTT_CONSUMER = 1;
    const SCOTT_HEALTH = 2;
    const SEBNA = 3;
    
    /* Revisionable */
    
    protected $revisionEnabled = true;
    
    protected $keepRevisionOf = array(
        'name','description','business_unit', 'supplier_code'
    );
    
    protected $revisionFormattedFieldNames = array(
        'name' => 'Name',
        'description' => 'Description',
        'business_unit' => 'Business Unit',
        'supplier_code' => 'Supplier',
    );
    
    public $saveCreateRevision = true;
    public $softDelete = true;
    public $revisionClassName =  "Order Process";
    public $revisionPrimaryIdentifier = "id";
    
    public $revisionRelations = ['reception','purchaseOrder','customsDeclaration','freight','shipment','document'];
    
    /* Elastic Search */
    
    //Indexing Enabled
    public $esEnabled = true;
    //Context for Indexing
    public $esContext = "order-tracking";
    //Main Document
    public $esMain = true;
    //Info Context
    public $esInfoContext = "order-tracking";
    //Remove
    public $esRemove = ['supplier_code'];
    
    /*
     * Event Observers
     */
    
    public static function boot() {
        parent:: boot();
        
        static::bootElasticSearchEvent();
        
        static::bootRevisionable();
    }    
    
    /*
     * Accessors
     */
    
    public function getBusinessUnitRevisionAttribute($val)
    {
        if(key_exists($val,self::$business_unit))
        {
            return self::$business_unit[$val];
        }
        else
        {
            return "";
        }        
    }
    
    public function getBusinessUnitEsAttribute($val)
    {
        return $this->getBusinessUnitRevisionAttribute($val);
    }

    public function getSupplierCodeRevisionAttribute($val)
    {
        $supplier = \JdeSupplierMaster::where('Supplier_Code','=',$val)->first();
        if($supplier)
        {
            return $supplier->Supplier_Name." (Code: ".$val.")";
        }

        return "";
    }

    public function getSupplierCodeEsAttribute($val)
    {
        return $this->getSupplierCodeRevisionAttribute($val);
    }

    public function getSupplierNameAttribute()
    {
        if($this->supplier)
        {
            return $this->supplier->Supplier_Name." (Code: {$this->supplier->Supplier_Code})";
        }

        return "";
    }
    
    /*
     * Utility Functions
     */
    
    public function getClassName()
    {
        return $this->revisionClassName;
    }
    
    public function getReadableName()
    {
        return $this->name." (Id:".$this->id.")";
    }
    
    public function getIcon()
    {
        return "fa-map-marker";
    }
    
    /*
     * Relationships
     */
    
    public function reception()
    {
        return $this->hasMany('SwiftReception','order_id');
    }
    
    public function freight()
    {
        return $this->hasMany('SwiftFreight','order_id');
    }
    
    public function storage()
    {
        return $this->hasMany('SwiftStorage','order_id');
    }
    
    public function shipment()
    {
        return $this->hasMany('SwiftShipment','order_id');
    }
    
    public function customsDeclaration()
    {
        return $this->hasMany('SwiftCustomsDeclaration','order_id');
    }

    public function supplier()
    {
        return $this->belongsTo('JdeSupplierMaster','supplier_code','Supplier_Code');
    }
    
    
    /*
     * Morphic
     */
    
    public function purchaseOrder()
    {
        return $this->morphMany('SwiftPurchaseOrder','purchasable');
    }    
    
    public function document()
    {
        return $this->morphMany('SwiftDocument','document');
    }
    
    public function flag()
    {
        return $this->morphMany('SwiftFlag','flaggable');
    }
    
    public function recent()
    {
        return $this->morphMany('SwiftRecent','recentable');
    }
    
    public function event()
    {
        return $this->morphMany('SwiftEvent','eventable');
    }

    public function subscription()
    {
        return $this->morphMany('SwiftSubscription','subscriptionable');
    }

    public function payable()
    {
        return $this->morphMany('SwiftACPRequest','payable');
    }
    
    
    /*
     * Polymorphic Relation
     */
    
    public function workflow()
    {
        return $this->morphOne('SwiftWorkflowActivity', 'workflowable');
    }
    
    public function message()
    {
        return $this->morphMany('SwiftMessage','messageable');
    }
    
    public function comments()
    {
        return $this->morphMany('SwiftComment', 'commentable');
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
     * Functions
     */
    
    public static function getById($id)
    {
        return self::with('purchaseOrder','reception','freight','shipment','customsDeclaration','storage','document')->find($id);
    }
    
    /*
     * Pusher Channel Name
     */
    
    public function channelName()
    {
        return "ot_".$this->id;
    }
    
    /*
     * Query
     */
    public static function getInProgress($limit=0,$important = false,$business_unit=0)
    {
        $query = self::query();
        if($limit > 0)
        {
            $query->take($limit);
        }
        
        if($business_unit > 0)
        {
            if(array_key_exists($business_unit,self::$business_unit))
            {
                $query->whereBusinessUnit($business_unit);
            }
            else
            {
                throw new Exception("Business unit with ID:".$business_unit." is not defined");
            }
        }
        
        return $query->orderBy('swift_order.updated_at','desc')
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
                            },($important === true ? ">" : "="),0)->remember(5)->get();        
    }
    
    public static function getInProgressResponsible($limit=0,$important=false,$business_unit=0)
    {
        $query = self::query();
        if($limit > 0)
        {
            $query->take($limit);
        }
        
        if($business_unit > 0)
        {
            if(array_key_exists($business_unit,self::$business_unit))
            {
                $query->whereBusinessUnit($business_unit);
            }
            else
            {
                throw new Exception("Business unit with ID:".$business_unit." is not defined");
            }
        }        
        
        return $query->orderBy('swift_order.updated_at','desc')
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
                            },($important === true ? ">" : "="),0)->remember(5)->get();       
    }
    
    public static function getInProgressCount($business_unit=0)
    {
        $query = self::query();
        
        if($business_unit > 0)
        {
            if(array_key_exists($business_unit,self::$business_unit))
            {
                $query->whereBusinessUnit($business_unit);
            }
            else
            {
                throw new Exception("Business unit with ID:".$business_unit." is not defined");
            }
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
                            },'=',0)->remember(5)->count();
    }
    
    public static function getInProgressWithEta($business_unit=0)
    {
        $query = self::query();
        
        if($business_unit > 0)
        {
            if(array_key_exists($business_unit,self::$business_unit))
            {
                $query->whereBusinessUnit($business_unit);
            }
            else
            {
                throw new Exception("Business unit with ID:".$business_unit." is not defined");
            }
        }        
        
        return $query->orderBy('swift_order.updated_at','asc')
                            ->with(array('nodes.permission' => function($q){
                                return $q->wherePermissionType(SwiftNodePermission::RESPONSIBLE);
                            },'workflow','workflow.nodes'))
                            ->whereHas('workflow',function($q){
                                return $q->where('status','=',SwiftWorkflowActivity::INPROGRESS,'AND')
                                        ->whereHas('nodes',function($q){
                                             return $q->where('user_id','=',0)->whereHas('definition',function($q){
                                                return $q->where('eta','>',0);
                                             });
                                        }); 
                            })->remember(5)->get();        
    }
}