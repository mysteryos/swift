<?php
class JdePurchaseOrderItem extends Eloquent {
    protected $connection = 'sct_jde';

    protected $table = 'sct_jde.jdepodetail';

    private static $cache_expiry_time = 240;

    public function order()
    {
        $this->order = \JdePurchaseOrder::where('Order Number','=',$this->Order_Number)
                        ->where('Order Type','=',$this->Order_Type)
                        ->remember(self::$cache_expiry_time)
                        ->get();
        
        return $this->order;
    }
}