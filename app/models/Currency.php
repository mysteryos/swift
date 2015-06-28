<?php
/**
 * Name: Currency Eloquent Model
 * Description: Table of currencies with their list of names and currency code
 *
 * @author kpudaruth
 */

class Currency extends Eloquent
{
    protected $table = "scott_swift.currency";
    
    protected $fillable = ['code','name'];

    protected $appends = ['fullname'];

    protected $primaryKey = "code";
    
    /*
     * Accessors
     */

    public function getFullnameAttribute()
    {
        return $this->code." - ".$this->name;
    }
    
    /*
     * Scope
     */
    
    /*
     * Relationships
     */
    
    /*
     * Query
     */

    public static function getAll()
    {
        $all = self::select(DB::raw('code, CONCAT(code," - ",name) as currency'))
                ->orderBy('code','ASC')
                ->get();

        $currencyArray = [];

        foreach($all as $c)
        {
            $currencyArray[$c->code] = $c->currency;
        }

        return $currencyArray;
    }
    
}
