<?php
/**
 * Description of SwiftSalesCommissionProductRate
 *
 * @author kpudaruth
 */
class SwiftSalesCommissionProductCategoryRate extends Eloquent {
    
    use Illuminate\Database\Eloquent\SoftDeletingTrait;
    
    protected $table = "swift_com_product_rate";
    
    protected $fillable = ['effective_date_start','effective_date_end','rate'];
    
    protected $dates = ['deleted_at','effective_date_start','effective_date_end'];
    
    

}
