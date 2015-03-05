<?php
/**
 * Description of SwiftACPRequest
 *
 * @author kpudaruth
 */
class SwiftACPRequest extends Eloquent 
{

    use \Illuminate\Database\Eloquent\SoftDeletingTrait;
    use \Venturecraft\Revisionable\RevisionableTrait;
    use \Swift\ElasticSearchEventTrait;
    
    protected $table = "swift_acp_request";
    
    protected $fillable = ['name','description','billable_company_code','owner_user_id','supplier_code'];

    protected $appends = ['company_name','supplier_name'];
    
    public $dates = ['deleted_at'];

    /* Elastic Search */
    
    //Indexing Enabled
    public $esEnabled = true;
    //Context for Indexing
    public $esContext = "acpayable";
    //Main Document
    public $esMain = true;
    //Info Context
    public $esInfoContext = "acpayable";
    public $esRemove = ['owner_user_id','supplier_name','company_name'];
    
    /* Revisionable */
    
    protected $revisionEnabled = true;
    
    protected $keepRevisionOf = array(
        'name','description','business_unit'
    );
    
    protected $revisionFormattedFieldNames = array(
        'name' => 'Name',
        'description' => 'Description',
        'billable_company_code' => 'Billable Company',
        'supplier_code' =>  'Supplier'
    );
    
    public $saveCreateRevision = true;
    public $softDelete = true;
    public $revisionClassName =  "Accounts Payable";
    public $revisionPrimaryIdentifier = "id";

    public $revisionRelations = ['invoice','payment','purchaseOrder','paymentVoucher','creditNote','approval','document'];
    
    /*
     * Event Observers
     */
    
    public static function boot() {
        parent:: boot();
        
        static::bootElasticSearchEvent();
        
        static::bootRevisionable();

        static::creating(function($model){
            $model->owner_user_id = Sentry::getUser()->id;
        });
    }    
    
    /*
     * Accessors
     */

    public function getSupplierName()
    {
        if($this->supplier)
        {
            return $this->supplier->Supplier_Name." (Code: ".$val.")";
        }

        return "";
    }

    public function getCompanyName()
    {
        if($this->company)
        {
            return $this->company->ALPH;
        }

        return "";
    }

    public function getSupplierCodeRevisionableAttribute($val)
    {
        $supplier = \JdeSupplierMaster::where('Supplier_Code','=',$val)->first();
        if($supplier)
        {
            return $supplier->Supplier_Name." (Code: ".$val.")";
        }
        
        return "(N/A)";
    }
    
    public function getBillableCompanyCodeRevisionableAttribute($val)
    {
        $company = \JdeCustomer::where('AN8','=',$val)->first();
        if($company)
        {
            return $company->ALPH;
        }
        return "(N/A)";
    }

    public function getBillableCompanyCodeEsAttribute($val)
    {
        return $this->getBillableCompanyCodeRevisionableAttribute($val);
    }

    public function getSupplierCodeEsAttribute($val)
    {
        return $this->getSupplierCodeRevisionableAttribute($val);
    }
    
    /*
     * Scope
     */
    
    /*
     * Utility
     */
    
    public function getClassName()
    {
        return $this->revisionClassName;
    }
    
    public function getReadableName()
    {
        if($this->name !== "")
        {
            return $this->company_name." | ".$this->supplier_name." - ".$this->name." (Id:".$this->id.")";
        }
        else
        {
            return $this->company_name." | ".$this->supplier_name." - (Id:".$this->id.")";
        }
    }
    
    public function getIcon()
    {
        return "fa-money";
    }

    /*
     * Pusher Channel Name
     */

    public function channelName()
    {
        return "acp_".$this->id;
    }
    
    /*
     * Relationship
     */
    
    public function supplier()
    {
        return $this->belongsTo('JdeSupplierMaster','supplier_code','Supplier_Code');
    }
    
    public function company()
    {
        return $this->belongsTo('JdeCustomer','billable_company_code','AN8');
    }
    
    public function owner()
    {
        return $this->belongsTo('User','owner_user_id');
    }
    
    public function invoice()
    {
        return $this->hasOne('SwiftACPInvoice','acp_id');
    }
    
    public function payment()
    {
        return $this->hasMany('SwiftACPPayment','acp_id');
    }
    
    public function purchaseOrder()
    {
        return $this->morphMany('SwiftPurchaseOrder','purchasable');
    }

    public function paymentVoucher()
    {
        return $this->hasMany('SwiftACPPaymentVoucher','acp_id');
    }

    public function creditNote()
    {
        return $this->hasMany('SwiftACPCreditNote','acp_id');
    }

    /*
     * Polymorphic Relation
     */

    public function workflow()
    {
        return $this->morphOne('SwiftWorkflowActivity', 'workflowable');
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

    public function approval()
    {
        return $this->morphMany('SwiftApproval','approvable');
    }
    

    /*
     * Query
     */

    public static function getById($id)
    {
        return self::with('suppler','company','owner','invoice','payment','purchaseOrder','paymentVoucher','creditNote','approval','document')
                    ->find($id);
    }

    public static function getInProgress($limit=0,$important = false,$billable_company=0)
    {
        $query = self::query();
        if($limit > 0)
        {
            $query->take($limit);
        }

        if($billable_company > 0)
        {
            $query->whereBillableCompanyCode($billable_company);
        }

        return $query->orderBy('swift_acp_request.updated_at','desc')
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

    public static function getInProgressResponsible($limit=0,$important=false,$billable_company=0)
    {
        $query = self::query();
        if($limit > 0)
        {
            $query->take($limit);
        }

        if($billable_company > 0)
        {
            $query->whereBillableCompanyCode($billable_company);
        }

        return $query->orderBy('swift_acp_request.updated_at','desc')
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

    public static function getInProgressCount($billable_company=0)
    {
        $query = self::query();

        if($billable_company > 0)
        {
            $query->whereBillableCompanyCode($billable_company);
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

    public static function getInProgressWithEta($billable_company=0)
    {
        $query = self::query();

        if($billable_company > 0)
        {
            $query->whereBillableCompanyCode($billable_company);
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

    public function isOwner($user_id=false)
    {
        if($user_id===false)
        {
            $user_id = \Sentry::getUser()->id;
        }

        return $this->owner_user_id === $user_id;
    }
     
    
}
