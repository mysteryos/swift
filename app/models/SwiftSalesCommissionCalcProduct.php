<?php
/**
 * Description of SwiftSalesCommissionCalcProduct
 *
 * @author kpudaruth
 */
class SwiftSalesCommissionCalcProduct extends Eloquent {

    use Illuminate\Database\Eloquent\SoftDeletingTrait;
    
    protected $table = "swift_com_sales_calc_prod";
    
    protected $fillable = ['calc_id','scheme_rate_id','scheme_id','jde_itm','jde_doc','jde_an8','jde_qty','total'];
    
    protected $dates = ['deleted_at'];
    
    /*
     * Relationships
     */
    
    public function calculation()
    {
        return $this->belongsTo('SwiftSalesCommissionCalc','calc_id')->withTrashed();
    }
    
    public function rate()
    {
        return $this->belongsTo('SwiftSalesCommissionSchemeRate','category_rate_id')->withTrashed();
    }
    
    public function category()
    {
        return $this->belongsTo('SwiftSalesCommissionScheme','scheme_id')->withTrashed();
    }
    
    public function jdeproduct()
    {
        return $this->belongsTo('JdeProduct','jde_itm','ITM');
    }
    
    public function customer()
    {
        return $this->belongsTo('JdeCustomer','jde_an8','AN8');
    }
    
}
