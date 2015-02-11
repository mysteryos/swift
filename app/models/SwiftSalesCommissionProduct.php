<?php
/**
 * Description of SwiftSalesCommisionProduct
 *
 * @author kpudaruth
 */
class SwiftSalesCommissionProduct extends Eloquent {

    use Illuminate\Database\Eloquent\SoftDeletingTrait;
    
    protected $table = "swift_com_product";
    
    protected $fillable = ['itm','category_id'];
    
    protected $dates = ['deleted_at'];
    
    public function jdeproduct()
    {
        return $this->belongsTo('JdeProduct','jde_itm','ITM');
    }
    
}
