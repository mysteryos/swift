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

    /*
     * Find Product By Name
     *
     * @param string $term
     * @param int $offset
     * @param int $limit
     * @return \Illuminate\Support\Collection
     */
    public static function getByName($term,$offset,$limit)
    {
        return self::where('DSC1','LIKE',"%$term%")
                ->whereIn('GLPT',['FOOD','HHPC','WINE'])
                ->limit($limit)
                ->offset($offset)
                ->orderBy('DSC1','ASC')
                ->remember(self::$cache_expiry_time)->get();        
    }

    /*
     * Find Product By Code
     *
     * @param string $term
     * @param int $offset
     * @param int $limit
     * @return \Illuminate\Support\Collection
     */
    public static function getByCode($term,$offset,$limit)
    {
        return self::where('AITM','LIKE',"%$term%")
                ->limit($limit)
                ->distinct()
                ->offset($offset)
                ->orderBy('AITM','ASC')
                ->remember(self::$cache_expiry_time)->get();        
    }

    /*
     * Count Product By Product
     *
     * @param string $term
     * @return integer
     */
    public static function countByName($term)
    {
        return self::where('DSC1','LIKE',"%$term%")
                ->remember(self::$cache_expiry_time)->count();        
    }

    /*
     * Count Product By Code
     *
     * @param string $term
     * @return integer
     */
    public static function countByCode($term)
    {
        return self::where('AITM','LIKE',"%$term%")
                ->distinct()
                ->remember(self::$cache_expiry_time)->count();        
    }      

    /*
     * Get Product By Name, Filtered by Nespresso Category Codes
     *
     * @param string $term
     * @param int $offset
     * @param int $limit
     * @return \Illuminate\Support\Collection
     */
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

    /*
     * Get Product By Code, Filtered by Nespresso Category Codes
     *
     * @param string $term
     * @param int $offset
     * @param int $limit
     * @return \Illuminate\Support\Collection
     */
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

    /*
     * Count Product By Name, Filtered by Nespresso Category Codes
     *
     * @param string $term
     * @return integer
     */
    public static function countNespressoMachineByName($term)
    {
        return self::where('DSC1','LIKE',"%$term%")
                ->where('SRP3','=','NES','AND')
                ->where('SRP4','=','NEM','AND')
                ->remember(self::$cache_expiry_time)->count();
    }

    /*
     * Count Product By Code, Filtered by Nespresso Category Codes
     *
     * @param string $term
     * @return integer
     */
    public static function countNespressoMachineByCode($term)
    {
        return self::where('LITM','LIKE',"%$term%")
                ->where('SRP3','=','NES','AND')
                ->where('SRP4','=','NEM','AND')
                ->distinct()
                ->remember(self::$cache_expiry_time)->count();
    }
}
