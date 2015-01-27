<?php
/*
 * Name: Jde Products on SCT_JDE
 * Description: Eloquent Model
 */

class JdeProduct extends Eloquent {
    protected $connection = 'sct_jde';
    
    protected $table = 'sct_jde.jdeproducts';
    
    protected $primaryKey = "ITM";
    
    public $timestamps = false;
    
    private static $cache_expiry_time = 240;
    
    public function getAITMAttribute($val)
    {
        return str_pad(trim($val), 5, '0', STR_PAD_LEFT);
    }
    
    public static function getByName($term,$offset,$limit)
    {
        return self::where('DSC1','LIKE',"%$term%")
                ->limit($limit)
                ->offset($offset)
                ->orderBy('DSC1','ASC')
                ->remember(self::$cache_expiry_time)->get();        
    }
    
    public static function getByCode($term,$offset,$limit)
    {
        return self::where('ITM','LIKE',"%$term%")
                ->limit($limit)
                ->distinct()
                ->offset($offset)
                ->orderBy('ITM','ASC')
                ->remember(self::$cache_expiry_time)->get();        
    }
    
    public static function countByName($term)
    {
        return self::where('DSC1','LIKE',"%$term%")
                ->remember(self::$cache_expiry_time)->count();        
    }
    
    public static function countByCode($term)
    {
        return self::where('ITM','LIKE',"%$term%")
                ->distinct()
                ->remember(self::$cache_expiry_time)->count();        
    }      
    
    public static function getNespressoMachineByName($term,$offset,$limit)
    {
        return self::where('DSC1','LIKE',"%$term%")
                ->where('SRP3','=','NES','AND')
                ->where('SRP4','=','NEM','AND')
                ->limit($limit)
                ->offset($offset)
                ->orderBy('DSC1','ASC')
                ->remember(self::$cache_expiry_time)->get();
    }
    
    public static function getNespressoMachineByCode($term,$offset,$limit)
    {
        return self::where('LITM','LIKE',"%$term%")
                ->where('SRP3','=','NES','AND')
                ->where('SRP4','=','NEM','AND')
                ->distinct()
                ->limit($limit)
                ->offset($offset)
                ->orderBy('LITM','ASC')
                ->remember(self::$cache_expiry_time)->get();
    }
    
    public static function countNespressoMachineByName($term)
    {
        return self::where('DSC1','LIKE',"%$term%")
                ->where('SRP3','=','NES','AND')
                ->where('SRP4','=','NEM','AND')
                ->remember(self::$cache_expiry_time)->count();
    }
    
    public static function countNespressoMachineByCode($term)
    {
        return self::where('LITM','LIKE',"%$term%")
                ->where('SRP3','=','NES','AND')
                ->where('SRP4','=','NEM','AND')
                ->distinct()
                ->remember(self::$cache_expiry_time)->count();
    }
}
