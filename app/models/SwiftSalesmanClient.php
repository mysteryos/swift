<?php
/**
 * Description of SwiftSalesmanClient
 *
 * @author kpudaruth
 */
class SwiftSalesmanClient extends Eloquent {
    
    use \Illuminate\Database\Eloquent\SoftDeletingTrait;
    use \Venturecraft\Revisionable\RevisionableTrait;       
    
    protected $table = "swift_salesman_client";
    
    protected $fillable = ['customer_code'];
    
    protected $dates = ['deleted_at'];
    
    protected $with = ['customer'];
    
    /* Revisionable */
    
    protected $revisionEnabled = true;
    protected $keepRevisionOf = array(
        'customer_code'
    );
    
    protected $revisionFormattedFieldNames = array(
        'customer_code' => 'Customer'
    );
    
    public $revisionClassName = "Salesman's clients";
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
    
    public function salesman()
    {
        return $this->belongsTo('SwiftSalesman','salesman_id');
    }
    
    public function customer()
    {
        return $this->belongsTo('JdeCustomer','customer_code','AN8');
    }
    
    
    /*
     * Utility
     */
    
    public static function getBySalesmanId($salesman_id)
    {
        return self::whereSalesmanId($salesman_id)->get();
    }
}
