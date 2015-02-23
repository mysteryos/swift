<?php

class SwiftDispatchClient extends Eloquent {
    
    use \Illuminate\Database\Eloquent\SoftDeletingTrait;
    use \Venturecraft\Revisionable\RevisionableTrait;   
    
    protected $table = "swift_dispatch_client";
    
    protected $fillable = ["driver_id","customer_code"];
    
    protected $with = ['customer'];
    
    protected $dates = ['deleted_at'];
    
    /* Revisionable */
    
    protected $revisionEnabled = true;
    protected $keepRevisionOf = array(
        'customer_code'
    );
    
    protected $revisionFormattedFieldNames = array(
        'customer_code' => 'Customer'
    );
    
    public $revisionClassName = "Dispatch clients";
    public $revisionPrimaryIdentifier = "customer_code";
    public $keepCreateRevision = true;
    public $softDelete = true;    
    
    /*
     * Event Observers
     */
    
    public static function boot() {
        parent:: boot();
        
        static::bootRevisionable();
    }        
    
    public function getCustomerCodeRevisionAttribute($val)
    {
        if((int)$val > 0)
        {
            $jdeCustomer = JdeCustomer::where('AN8','=',$val)->first();
            if($jdeCustomer)
            {
                return trim($jdeCustomer->ALPH." (Code: ".$jdeCustomer->AN8.")");
            }
        }
        return "";
    }
    
    /*
     * Relationships
     */
    
    public function driver()
    {
        return $this->belongsTo('SwiftDriver','driver_id');
    }
    
    public function customer()
    {
        return $this->belongsTo('JdeCustomer','customer_code','AN8');
    }
    
}