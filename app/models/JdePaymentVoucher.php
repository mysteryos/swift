<?php
/*
 * Name: JDE Payment Voucher
 * Description:
 */

class JdePaymentVoucher extends Eloquent {
    protected $connection = 'sct_jde';

    protected $table = "jdepaymvouchersmaster";

    public function getKcoAttribute($value)
    {
        return (int)$value;
    }
}

