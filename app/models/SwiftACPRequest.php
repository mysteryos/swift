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
    public $esRemove = ['owner_user_id'];
    
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
    
    public function getSupplierCodeRevisionableAttribute($val)
    {
        $supplier = \JdeSupplierMaster::where('Supplier_Code','=',$val)->get();
        if($supplier)
        {
            return $supplier->Supplier_Name." (Code: ".$val.")";
        }
        
        return "(N/A)";
    }
    
    public function getBillableCompanyCodeRevisionableAttribute($val)
    {
        $company = \JdeCustomer::where('AN8','=',$val)->get();
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
        return $this->name." (Id:".$this->id.")";
    }
    
    public function getIcon()
    {
        return "fa-money";
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
        return $this->hasOne('SwiftAPCInvoice','acp_id');
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
     * Query
     */
    
}
