<?php
/**
 * Description of Currency
 *
 * @author kpudaruth
 */

class Currency extends Eloquent
{
    protected $table = "currency";
    
    protected $fillable = ['code','name'];

    protected $appends = ['fullname'];
    
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

    public function getAll()
    {
        $all = self::select('id',DB::raw('CONCATENATE(code," - ",name) as currency'))
                ->orderBy('code','ASC')
                ->get();

        $currencyArray = [];

        foreach($all as $c)
        {
            $currencyArray[$c->id] = $c->currency;
        }

        return $currencyArray;
    }
    
}
