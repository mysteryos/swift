<?php
/**
 * Description of SwiftSalesCommissionCalc
 *
 * @author kpudaruth
 */
class SwiftSalesCommissionCalc extends Eloquent {
    
    use Illuminate\Database\Eloquent\SoftDeletingTrait;
    
    protected $table = 'swift_com_sales_calc';
    
    protected $fillable = ['salesman_id','budget_id','value','date_start','date_end'];
    
    protected $dates = ['date_start','date_end','deleted_at'];
    
    /*
     * Relationships
     */
    
    public function salesman()
    {
        return $this->belongsTo('SwiftSalesman','salesman_id')->withTrashed();
    }
    
    public function budget()
    {
        return $this->belongsTo('SwiftSalesCommissionBudget','budget_id')->withTrashed();
    }
}
