<?php
class JdePurchaseOrder extends Eloquent
{
    protected $connection = 'sct_jde';

    protected $table = 'sct_jde.jdepoheader';

    protected $appends = ['name'];

    protected $with = ['supplier','shipto'];

    public $dates = ['Order_Date','Delivery_Date'];

    private static $cache_expiry_time = 240;

    public static function findByNumberAndType($number,$type)
    {
        return self::where('Order_Number','=',$number)
                ->where('Order_Type','=',$type,'AND')
                ->remember(self::$cache_expiry_time)
                ->first();
    }

    public function getNameAttribute()
    {
        return $this->Order_Number." ".$this->Order_Type;
    }

    public function item()
    {
        return $this->hasMany('JdePurchaseOrderItem','order_id')->orderBy('Line_Number');
    }

    public function supplier()
    {
        return $this->hasOne('JdeSupplierMaster','Supplier_Code','Supplier_Number');
    }

    public function shipto()
    {
       return $this->hasOne('JdeSupplierMaster','Supplier_Code','Ship_To');
    }
}