<?php
/*
 * Name: Jde Supplier Master on SCT_JDE
 * Description: Eloquent Model
 */

class JdeSupplierMaster extends eloquent {
    protected $connection = 'sct_jde';
    
    protected $table = 'sct_jde.jdesuppliermaster';
    
    public static function getByName($term,$offset,$limit)
    {
        return self::where('Supplier_Name','LIKE',"%$term%")->limit($limit)->offset($offset)->get();
    }
    
    public static function getByCode($term,$offset,$limit)
    {
        return self::where('Supplier_Code','LIKE',"%$term%")->limit($limit)->offset($offset)->get();
    }
    
    public static function countByName($term)
    {
        return self::where('Supplier_Name','LIKE',"%$term%")->count();
    }
    
    public static function countByCode($term)
    {
        return self::where('Supplier_Code','LIKE',"%$term%")->count();
    }
}
