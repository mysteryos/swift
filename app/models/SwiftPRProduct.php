<?php
/*
 * Name: Swift A&P Product
 * Description:
 */

class SwiftPRProduct extends Eloquent {
    use Illuminate\Database\Eloquent\SoftDeletingTrait;
    use \Venturecraft\Revisionable\RevisionableTrait; 
    use \Swift\ElasticSearchEventTrait;

    public $readableName = "Product Returns";

    protected $table = "swift_pr_product";
    
    protected $fillable = array("pr_id","jde_itm","qty_client","qty_pickup","qty_store",
                                "qty_triage_picking","qty_triage_disposal","invoice_id","invoice_recognition","price","reason_id","reason_others","pickup");
    
    protected $attributes = array('pickup'=>self::PICKUP);
    
    protected $guarded = array('id');
    
    protected $appends = array('name','reason_text');

    protected $with = ['reason','approvalretailman'];
    
    protected $dates = ['deleted_at'];
    
    /* Revisionable */
    
    protected $revisionEnabled = true;
    
    protected $keepRevisionOf = array(
                                    'jde_itm',
                                    'reason_id',
                                    'reason_others',
                                    'qty_client',
                                    'qty_pickup',
                                    'qty_store',
                                    'qty_triage_picking',
                                    'qty_triage_disposal',
                                    'invoice_id',
                                    'pickup'
                                );
    
    protected $revisionFormattedFieldNames = array(
                                                'jde_itm' => 'JDE Id',
                                                'qty_client' => 'Quantity Client',
                                                'qty_pickup' => 'Quantity Pickup',
                                                'qty_store' => 'Quantity Store',
                                                'qty_triage_picking' => 'Quantity Picking',
                                                'qty_triage_disposal' => 'Quantity Disposal',
                                                'invoice_id' => 'Invoice Number',
                                                'price' => 'Price',
                                                'reason_id' => 'Reason',
                                                'reason_others' => 'Reason(specify)',
                                                'pickup' => "Pickup",
                                            );
    
    public static $revisionName = "Product";
    
    public $revisionClassName = "Product";
    public $revisionPrimaryIdentifier = "name";
    public $keepCreateRevision = true;
    public $softDelete = true;
    public $revisionDisplayId = true;
    
    /*
     * Constants: Pickup
     */
    
    const PICKUP = 1;
    const NO_PICKUP = 0;

    /*
     * Constants: Invoice Recognition
     */

    const INVOICE_AUTO = 1;
    const INVOICE_MANUAL = 2;

    /*
     * Elastic Search Indexing
     */

    //Indexing Enabled
    public $esEnabled = true;
    //Context for Indexing
    public $esContext = "product-returns";
    public $esInfoContext = "product";
    public $esRemove = ['pr_id','pickup','reason_id','invoice_recognition','price'];

    /*
     * ElasticSearch Utility Id
     */

    public function esGetId()
    {
        return $this->pr_id;
    }

    public function esGetParent()
    {
        return $this->pr;
    }

    /*
     * Event Observers
     */

    public static function boot() {
        parent:: boot();

        static::bootElasticSearchEvent();

        static::bootRevisionable();

    }
    
    /*
     * Revisionable Accessors
     */

    public function getReasonIdRevisionAttribute($val)
    {
        if($val > 0)
        {
            return \SwiftPRReason::find($val)->text;
        }

        return "";
    }

    /*
     * Accessor
     */
    public function getNameAttribute()
    {
        if($this->ITM !== "" && count($this->jdeproduct) !== 0)
        {
            return trim($this->jdeproduct->DSC1);
        }
        
        return "";
    }
    
    public function getReasonTextAttribute()
    {
        if((int)$this->reason_id > 0)
        {
            return $this->reason->text;
        }

        return "";
    }    
    
    /*
     * Relationships
     */
    public function pr()
    {
        return $this->belongsTo('SwiftPR','pr_id');
    }
    
    public function jdeproduct()
    {
        return $this->belongsTo('JdeProduct','jde_itm','ITM');
    }
    
    public function approvalretailman()
    {
        return $this->morphOne('SwiftApproval','approvable')->with('comment')->where('type','=',SwiftApproval::PR_RETAILMAN);
    }

    public function discrepancy()
    {
        return $this->hasMany('SwiftPRDiscrepancy','product_id');
    }

    public function reason()
    {
        return $this->belongsTo('SwiftPRReason','reason_id');
    }
    
    /*
     * Utility
     */

    public function getApprovalStatus()
    {
        if($this->approvalretailman)
        {
            return $this->approvalretailman->approved;
        }

        return \SwiftApproval::PENDING;
    }
    
    
}
