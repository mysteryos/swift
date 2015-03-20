<?php
class JdePurchaseOrder extends Eloquent {
    protected $connection = 'sct_jde';

    protected $table = 'sct_jde.jdepoheader';

    private static $cache_expiry_time = 240;

    public static function findByNumberAndType($number,$type)
    {
        return self::where('Order_Number','=',$number)
                ->where('Order_Type','=',$type,'AND')
                ->remember(self::$cache_expiry_time)
                ->first();
    }

    public function item()
    {
        $this->item = \JdePurchaseOrderItem::where('Order_Number','=',$this->Order_Number)
                        ->where('Order Type','=',$this->Order_Type)
                        ->remember(self::$cache_expiry_time)
                        ->get();

        return $this->item;
    }
}