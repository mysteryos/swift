<?php
/*
 * Name: Jde Products on SCT_JDE
 * Description: Eloquent Model
 */

class JdeProduct extends Eloquent {
    protected $connection = 'sct_jde';
    
    protected $table = 'sct_jde.jdeproducts';
    
    public static function getByName($term,$offset,$limit)
    {
        return self::where('DSC1','LIKE',"%$term%")
                ->limit($limit)
                ->offset($offset)
                ->orderBy('DSC1','ASC')
                ->get();        
    }
    
    public static function getByCode($term,$offset,$limit)
    {
        return self::where('AITM','LIKE',"%$term%")
                ->limit($limit)
                ->distinct()
                ->offset($offset)
                ->orderBy('AITM','ASC')
                ->get();        
    }
    
    public static function countByName($term)
    {
        return self::where('DSC1','LIKE',"%$term%")
                ->count();        
    }
    
    public static function countByCode($term)
    {
        return self::where('AITM','LIKE',"%$term%")
                ->distinct()
                ->count();        
    }      
    
    public static function getNespressoMachineByName($term,$offset,$limit)
    {
        return self::where('DSC1','LIKE',"%$term%")
                ->where('SRP3','=','NES','AND')
                ->where('SRP4','=','NEM','AND')
                ->limit($limit)
                ->offset($offset)
                ->orderBy('DSC1','ASC')
                ->get();
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
                ->get();
    }
    
    public static function countNespressoMachineByName($term)
    {
        return self::where('DSC1','LIKE',"%$term%")
                ->where('SRP3','=','NES','AND')
                ->where('SRP4','=','NEM','AND')
                ->count();
    }
    
    public static function countNespressoMachineByCode($term)
    {
        return self::where('LITM','LIKE',"%$term%")
                ->where('SRP3','=','NES','AND')
                ->where('SRP4','=','NEM','AND')
                ->distinct()
                ->count();
    }
}
