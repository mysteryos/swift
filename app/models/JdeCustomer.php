<?php
/*
 * Name: Jde Customer on SCT_JDE
 * Description: Eloquent Model
 */

class JdeCustomer extends Eloquent {
    protected $connection = 'sct_jde';
    
    protected $table = 'sct_jde.jdecustomers';

    protected $primaryKey = 'AN8';
    
    private static $cache_expiry_time = 240;
    
    public static function getByName($term,$offset,$limit)
    {
        return self::where('alph','LIKE',"%$term%")
                ->limit($limit)
                ->offset($offset)
                ->select('ALPH','AN8','AC09')
                ->remember(self::$cache_expiry_time)
                ->get();
    }
    
    public static function getByCode($term,$offset,$limit)
    {
        return self::where('an8','LIKE',"%$term%")
                ->distinct()
                ->limit($limit)
                ->offset($offset)
                ->select('ALPH','AN8','AC09')
                ->remember(self::$cache_expiry_time)
                ->get();
    }

    public static function getIn(array $in,$offset,$limit)
    {
        return self::whereIn('an8',$in)
                ->distinct()
                ->limit($limit)
                ->offset($offset)
                ->select('ALPH','AN8','AC09')
                ->remember(self::$cache_expiry_time)
                ->get();
    }
    
    public static function countByName($term)
    {
        return self::where('alph','LIKE',"%$term%")
                ->remember(self::$cache_expiry_time)
                ->count();
    }
    
    public static function countByCode($term)
    {
        return self::where('an8','LIKE',"%$term%")
                ->remember(self::$cache_expiry_time)
                ->count();
    }

    public function getReadableName()
    {
        return $this->ALPH." (Code: ".$this->AN8.")";
    }

    /*
     * Relationships
     */

    public function pr()
    {
        return $this->hasMany('SwiftPR','customer_code');
    }
}
