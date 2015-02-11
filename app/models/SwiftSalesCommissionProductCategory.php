<?php
/**
 * Description of SwiftSalesCommissionProductCategory
 *
 * @author kpudaruth
 */
class SwiftSalesCommissionProductCategory extends Eloquent {

    use Illuminate\Database\Eloquent\SoftDeletingTrait;
    
    protected $table = "swift_com_product_category";
    
    protected $fillable = ['name'];
    
    protected $dates = ['deleted_at'];
    
    
}
