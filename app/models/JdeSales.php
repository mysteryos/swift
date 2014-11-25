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
}