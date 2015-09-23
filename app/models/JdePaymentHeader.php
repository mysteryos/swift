<?php
/*
 * Name: JDE Payment Header
 * Description: 1st Part of Payment Table
 */

class JdePaymentHeader extends Eloquent {
    protected $connection = "sct_jde";

    protected $table = "sct_jde.jdepaymenthdrmaster";

    protected $primaryKey = "pyid";

    public $dates = ['dmtj'];


    /*
     * Accessors
     */
    public function getBankAccountNoAttribute($value)
    {
        return trim($value);
    }

    /*
     * Relationships
     */
    public function detail()
    {
        return $this->hasMany('JdePaymentDetail','pyid')->orderBy('rc5');
    }

    public function currency()
    {
        return $this->belongsTo('Currency','crrd');
    }

    public function supplier()
    {
        return $this->hasOne('JdeSupplierMaster','Supplier_Code','pye');
    }

    
}

