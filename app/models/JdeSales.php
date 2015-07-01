<?php

/*
 * Name: JDE Sales
 * Description: Contains all products found in each invoice output by JDE
 */

class JdeSales extends Eloquent {
    protected $connection = 'sct_jde';
    
    protected $table = 'sct_jde.jdesales';
    
    private static $cache_expiry_time = 240;

    /*
     * Find product by code, order by highest sales price
     *
     * @param integer $productCode
     * @return \Illuminate\Database\Eloquent\Model
     */
    public static function getProductHighestPrice($productCode)
    {
        return self::remember(self::$cache_expiry_time)
              ->where('UPRC','>',DB::raw(0))
              ->where('ITM','=',$productCode,'AND')
              ->whereIn('DCTO',array('3S','4S','S9'),'AND')
              ->orderBy('UPRC','DESC')->take(1)
              ->get();
    }

    /*
     * Find product by code, order by highest cost price
     *
     * @param integer $productCode
     * @return \Illuminate\Database\Eloquent\Model
     */
    public static function getProductLatestCostPrice($productCode)
    {
        return self::remember(self::$cache_expiry_time)
            ->where('ECST','>',\DB::raw(0))
            ->whereIn('DCTO',['3S','4S'],'AND')
            ->where('ITM','=',$productCode,'AND')
            ->orderBy('IVD','DESC')->take(1)->get()->first();
    }

    /*
     * Find products by invoice code
     *
     * @param integer $invoiceCode
     * @param integer $offset
     * @param integer $limit
     * @return \Illuminate\Support\Collection
     */
    public static function getByInvoiceCode($invoiceCode,$offset,$limit)
    {
        return self::remember(self::$cache_expiry_time)
            ->limit($limit)
            ->offset($offset)
            ->where('DOC','LIKE',"%$invoiceCode%")
            ->whereIn('DCTO',array('3S','4S','S9'),'AND')
            ->orderBy('DOC','ASC')
            ->groupBy('DOC')
            ->join('jdecustomers','jdecustomers.AN8','=','jdesales.AN8')
            ->select('DOC','IVD','jdesales.AN8','DCTO','jdecustomers.ALPH')
            ->get();
    }

    /*
     * Total number of invoices, by invoice code
     *
     * @param integer $invoiceCode
     * @return \Illuminate\Support\Collection
     */
    public static function totalByInvoiceCode($invoiceCode)
    {
        return count(self::remember(self::$cache_expiry_time)
            ->where('DOC','LIKE',"%$invoiceCode%")
            ->whereIn('DCTO',array('3S','4S','S9'),'AND')
            ->groupBy('DOC')
            ->get());
    }

    /*
     * Get products by invoice code
     *
     * @param integer $invoiceCode
     * @return \Illuminate\Support\Collection
     */
    public static function getProducts($invoiceCode)
    {
        return self::remember(self::$cache_expiry_time)
            ->where('DOC','=',$invoiceCode)
            ->with('product')
            ->orderBy('LNID','ASC')
            ->get();
    }

    /*
     * Relationships
     */
    public function customer()
    {
        return $this->belongsTo('JdeCustomer','AN8','AN8');
    }

    public function product()
    {
        return $this->belongsTo('JdeProduct','ITM','ITM');
    }
}