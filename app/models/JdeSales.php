<?php
class JdeSales extends Eloquent {
    protected $connection = 'sct_jde';
    
    protected $table = 'sct_jde.jdesales';
    
    private static $cache_expiry_time = 240;
    
    public static function getProductHighestPrice($productCode)
    {
        return self::where('UPRC','>',DB::raw(0))
              ->where('ITM','=',$productCode,'AND')
              ->whereIn('DCTO',array('3S','4S','S9'),'AND')
              ->orderBy('UPRC','DESC')->take(1)
              ->remember(self::$cache_expiry_time)->get();
    }
    
    public static function getProductLatestCostPrice($productCode)
    {
        return self::where('UPRC','>',DB::raw(0))
            ->where('ITM','=',$productCode,'AND')
            ->remember(self::$cache_expiry_time)
            ->orderBy('IVD','DESC')->take(1)->first();
    }

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
            ->select('DOC','IVD','JdeSales.AN8','DCTO','jdecustomers.ALPH')
            ->get();
    }

    public static function totalByInvoiceCode($invoiceCode)
    {
        return count(self::remember(self::$cache_expiry_time)
            ->where('DOC','LIKE',"%$invoiceCode%")
            ->whereIn('DCTO',array('3S','4S','S9'),'AND')
            ->groupBy('DOC')
            ->get());
    }

    public static function getProducts($invoiceCode)
    {
        return self::remember(self::$cache_expiry_time)
            ->where('DOC','=',$invoiceCode)
            ->with('product')
            ->orderBy('LNID','ASC')
            ->get();
    }

    public function customer()
    {
        return $this->belongsTo('JdeCustomer','AN8','AN8');
    }

    public function product()
    {
        return $this->belongsTo('JdeProduct','ITM','ITM');
    }
}