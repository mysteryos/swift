<?php

class SwiftSalesCommissionCalcType extends Eloquent {
    use Illuminate\Database\Eloquent\SoftDeletingTrait;
    
    protected $table = 'swift_com_sales_calc_type';
    
    protected $fillable = ['name','department_id'];
    
    
}