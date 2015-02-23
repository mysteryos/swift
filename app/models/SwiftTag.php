<?php
/*
 * Name: Swift Tag
 * Description: Handles tags for everything and anything
 */

class SwiftTag extends eloquent {
    use \Venturecraft\Revisionable\RevisionableTrait;    
    use Illuminate\Database\Eloquent\SoftDeletingTrait;
    
    protected $table = "swift_tag";
    
    protected $guarded = array('id');
    
    protected $fillable = ["taggable_id","taggable_type","type","user_id"];
    
    protected $dates = ['deleted_at'];
    
    public $timestamps = true;
    
    /* Revisionable */
    
    protected $revisionEnabled = true;
    
    protected $keepRevisionOf = array(
        'type'
    );
    
    protected $revisionFormattedFieldName = array(
        'type' => 'Tag'
    );
    
    protected $keepCreateRevision = true;
    
    /*
     * Order Tracking Tags
     */
    const OT_PURCHASE_ORDER = 7;
    const OT_BILL_OF_LADING = 8;
    const OT_FINAL_INVOICE = 9;
    const OT_PROFORMA_INVOICE = 10;
    const OT_BILL_OF_ENTRY = 11;
    const OT_NOTICE_OF_ARRIVAL = 12;
    const OT_COSTING = 13;
    const OT_GRN = 14;
    const OT_PACKING_LIST = 15;
    const AP_EVENTFLYER = 16;
    const PR_RETURNRECEIPT = 17;
    const PR_PRODUCTIMAGE = 18;
        
    /*
     * Compilation of Order Tracking Tags
     */
    public static $orderTrackingTags = [self::OT_PROFORMA_INVOICE => "Proforma invoice",
                                            self::OT_PURCHASE_ORDER => "Purchase Order",
                                            self::OT_FINAL_INVOICE => "Final Invoice",
                                            self::OT_BILL_OF_LADING=>"Bill of Lading",
                                            self::OT_PACKING_LIST=>"Packing List",
                                            self::OT_NOTICE_OF_ARRIVAL=>"Notice of Arrival",
                                            self::OT_BILL_OF_ENTRY=>"Bill of Entry",
                                            self::OT_COSTING=>"Costing",
                                            self::OT_GRN=>"Goods Received Note"];
    
    /*
     * Compilation of A&P Request Tags
     */
    public static $aprequestTags = [self::AP_EVENTFLYER => "Event flyer"];
    
    /*
     * Compilation of Product Returns Tags
     */
    
    public static $prTags = [self::PR_RETURNRECEIPT => "Return Receipt",self::PR_PRODUCTIMAGE => "Products"];
    
    /*
     * Event Observers
     */
    
    public static function boot() {
        parent:: boot();
        
        /*
         * Set User Id on Save
         */
        static::saving(function($model){
            $model->user_id = Sentry::getUser()->id;
        });
    }
    
    /*
     * Polymorphic Relation
     */
    
    public function taggable()
    {
        return $this->morphTo();
    }
    
    public function document()
    {
        return $this->morphedByMany('SwiftDocument', 'taggable');
    }
    
}