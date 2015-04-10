<?php
/**
 * Description of PaymentTerm
 *
 * @author kpudaruth
 */

class PaymentTerm extends Eloquent
{
    protected $table = "payment_term";
    
    protected $fillable = ['jde_code','name'];
    
    public $timestamps = false;
    
    /*
     * Accessors
     */
    
    /*
     * Scope
     */
    
    /*
     * Relationships
     */

    public function supplierPayment()
    {
        return $this->belongsTo('SupplierPaymentTerm','term_id');
    }
    
    /*
     * Query
     */

    public static function getAll()
    {
        $results = self::orderBy('jde_code','ASC')->remember(1440)->get();
        $resultArray = [];
        foreach($results as $r)
        {
            $resultArray[$r->id] = $r->name." ($r->jde_code)";
        }

        return $resultArray;
    }
    
}
