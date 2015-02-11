<?php
/**
 * Description of SwiftSalesCommissionBudget
 *
 * @author kpudaruth
 */

class SwiftSalesCommissionBudget extends Eloquent {

    use Illuminate\Database\Eloquent\SoftDeletingTrait;
    
    protected $table = "swift_com_salesman_budget";
    
    protected $fillable = ['date_start','date_end','value','date_type'];
    
    protected $dates = ['deleted_at','date_start','date_end'];
    
    /*
     * Relationships
     */
    
    public function salesman()
    {
        return $this->belongsTo('SwiftSalesman','salesman_id');
    }
}
