<?php
class JdeSales extends Eloquent {
    protected $connection = 'sct_jde';
    
    protected $table = 'sct_jde.jdesales';
    
    public static function getProductHighestPrice($productCode)
    {
        self::where('UPRC','>',0,'AND')
              ->where('ITM','=',$productCode,'AND')
              ->whereIn('DCTO',array('3S','4S','S9'))
              ->orderBy('UPRC','DESC')
              ->first();
    }
}