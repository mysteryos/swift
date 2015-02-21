<?php
/**
 * Description of SwiftSalesCommissionCalc
 *
 * @author kpudaruth
 */
class SwiftSalesCommissionCalc extends Eloquent {
    
    use Illuminate\Database\Eloquent\SoftDeletingTrait;
    
    protected $table = 'swift_com_sales_calc';
    
    protected $fillable = ['salesman_id','budget_id','type','scheme_id','rate_id','total','value','date_start','date_end','budget_info','scheme_info','rate_info','salesman_info'];
    
    protected $dates = ['date_start','date_end','deleted_at'];
    
    /*
     * Pusher Channel
     */
    
    public function getChannelName()
    {
        return "commission_calc_".$this->id;
    }
    
    /*
     * Accessors
     */
    
    public function getSchemeInfoDataAttribute()
    {
        return json_decode($this->scheme_info);
    }
    
    public function getRateInfoDataAttribute()
    {
        return json_decode($this->rate_info);
    }
    
    public function getSalesmanInfoDataAttribute()
    {
        return json_decode($this->salesman_info);
    }
    
    public function getBudgetInfoDataAttribute()
    {
        return json_decode($this->budget_info);
    }
    
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
    
    public function product()
    {
        return $this->hasMany('SwiftSalesCommissionCalcProduct','calc_id');
    }
    
    public function scheme()
    {
        return $this->belongsTo('SwiftSalesCommissionScheme','scheme_id');
    }
    
    public function rate()
    {
        return $this->belongsTo('SwiftSalesCommissionSchemeRate','rate_id');
    }
}
