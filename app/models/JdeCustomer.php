<?php
/*
 * Name: Jde Customer on SCT_JDE
 * Description: Eloquent Model
 */

class JdeCustomer extends eloquent {
    protected $connection = 'sct_jde';
    
    protected $table = 'jdecustomers';
    
    public static function getByName($term,$offset,$limit)
    {
        return self::where('alph','LIKE',"%$term%")->limit($limit)->offset($offset)->get();
    }
    
    public static function getByCode($term,$offset,$limit)
    {
        return self::where('an8','LIKE',"%$term%")->distinct()->limit($limit)->offset($offset)->get();
    }
    
    public static function countByName($term)
    {
        return self::where('alph','LIKE',"%$term%")->count();
    }
    
    public static function countByCode($term)
    {
        return self::where('an8','LIKE',"%$term%")->count();
    }
}
