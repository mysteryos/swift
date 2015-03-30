<?php
class JdePurchaseOrder extends Eloquent
{
    protected $connection = 'sct_jde';

    protected $table = 'sct_jde.jdepoheadermaster';

    protected $appends = ['name'];

    protected $with = ['supplier','shipto'];

    public $dates = ['order_date','delivery_date'];

    private static $cache_expiry_time = 240;

    public static function findByNumberAndType($number,$type)
    {
        return self::where('order_Number','=',$number)
                ->where('order_Type','=',$type,'AND')
                ->remember(self::$cache_expiry_time)
                ->first();
    }

    public function getNameAttribute()
    {
        return $this->order_number." ".$this->order_type;
    }

    public function item()
    {
        return $this->hasMany('JdePurchaseOrderItem','order_id')->orderBy('line_number');
    }

    public function supplier()
    {
        return $this->hasOne('JdeSupplierMaster','Supplier_Code','supplier_number');
    }

    public function shipto()
    {
       return $this->hasOne('JdeSupplierMaster','Supplier_Code','ship_to');
    }
}