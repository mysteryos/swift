<?php
/*
 * Name: JDE Payment Detail
 * Description: 2nd Part of Payment Table
 */

class JdePaymentDetail extends Eloquent {
    protected $connection = "sct_jde";

    protected $table = "jdepaymentdetmaster";

    /*
     * Accessors
     */

    public function getCoAttribute($value)
    {
        return (int)$value;
    }


    /*
     * Relationships
     */
    public function header()
    {
        return $this->belongsTo('JdePaymentHeader','pyid');
    }
}

