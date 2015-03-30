<?php
class JdePurchaseOrderItem extends Eloquent {
    protected $connection = 'sct_jde';

    protected $table = 'sct_jde.jdepodetailmaster';

    protected $with = ['product'];

    private static $cache_expiry_time = 240;

    public function order()
    {
        return $this->hasOne('JdePurchaseOrder','order_id');
    }

    public function product()
    {
        return $this->hasOne('JdeProduct','ITM','ITM');
    }
}