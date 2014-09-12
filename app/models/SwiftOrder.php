<?php
/*
 * Name: Swift Order
 * Description: Table that contains all orders
 */



class SwiftOrder extends Eloquent {
    
    use \Illuminate\Database\Eloquent\SoftDeletingTrait;    
    use \Venturecraft\Revisionable\RevisionableTrait;
    
    protected $table = "swift_order";
    
    protected $guarded = array('id');
    
    protected $fillable = array('name','description','business_unit','data');
    
    public $timestamps = true;
    
    public $dates = ['deleted_at'];
    
    public static $business_unit = array(self::SCOTT_CONSUMER=>'Scott Consumer',self::SCOTT_HEALTH=>'Scott Health',self::SEBNA=>'Sebna');
    
    const SCOTT_CONSUMER = 1;
    const SCOTT_HEALTH = 2;
    const SEBNA = 3;
    
    /* Revisionable */
    
    protected $revisionEnabled = true;
    
    protected $keepRevisionOf = array(
        'name','description','business_unit'
    );
    
    protected $revisionFormattedFieldNames = array(
        'name' => 'Name',
        'description' => 'Description',
        'business_unit' => 'Business Unit'
    );
    
    protected $revisionClassName = "Order Process";
    
    protected $saveCreateRevision = true;
    
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
    
    public function getClassName()
    {
        return $this->revisionClassName;
    }
    
    /*
     * Relationships
     */
    
    public function purchaseOrder()
    {
        return $this->hasMany('SwiftPurchaseOrder','order_id');
    }
    
    public function reception()
    {
        return $this->hasMany('SwiftReception','order_id');
    }
    
    public function freight()
    {
        return $this->hasMany('SwiftFreight','order_id');
    }
    
    public function shipment()
    {
        return $this->hasMany('SwiftShipment','order_id');
    }
    
    public function customsDeclaration()
    {
        return $this->hasMany('SwiftCustomsDeclaration','order_id');
    }
    
    
    /*
     * Morphic
     */
    public function document()
    {
        return $this->MorphMany('SwiftDocument','document');
    }
    
    public function flag()
    {
        return $this->MorphMany('SwiftFlag','flaggable');
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
    
    /*
     * Functions
     */
    
    public static function getById($id)
    {
        return self::with('purchaseOrder','reception','freight','shipment','customsDeclaration','document')->find($id);
    }
    
}