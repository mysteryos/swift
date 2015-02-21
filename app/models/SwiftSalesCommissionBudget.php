<?php
/**
 * Description of SwiftSalesCommissionBudget
 *
 * @author kpudaruth
 */

class SwiftSalesCommissionBudget extends Eloquent {

    use Illuminate\Database\Eloquent\SoftDeletingTrait;
    use \Venturecraft\Revisionable\RevisionableTrait;  
    
    protected $table = "swift_com_salesman_budget";
    
    protected $fillable = ['date_start','date_end','value','date_type'];
    
    protected $dates = ['deleted_at','date_start','date_end'];
    
    protected $appends = ['isActive'];
    
    
    /* Revisionable */
    
    protected $revisionEnabled = true;
    protected $keepRevisionOf = array(
        'date_start','date_end','value'
    );
    
    protected $revisionFormattedFieldNames = array(
        'date_start' => 'Budget Start Date',
        'date_end'  =>  'Budget End Date',
        'value'     =>  'Budget Value',
        'id'        => 'Id'
    );
    
    public $revisionClassName = "Salesman Budget";
    public $revisionPrimaryIdentifier = "id";
    public $keepCreateRevision = true;
    public $softDelete = true;
    
    //Date Types
    const DATE_MONTH = 1;
    const DATE_SEMESTER = 2;
    const DATE_YEAR = 3;
    
    public static function boot() {
        parent:: boot();
        
        static::bootRevisionable();
    }
    
    /*
     * Accessors
     */
    
    public function getIsActiveAttribute()
    {
        if($this->date_start !== null && $this->date_end !== null)
        {
            if(Carbon::now()->between($this->date_start,$this->date_end))
            {
                return true;
            }
        }
        return false;
    }
    
    /*
     * Relationships
     */
    
    public function salesman()
    {
        return $this->belongsTo('SwiftSalesman','salesman_id');
    }
    
    public function scheme()
    {
        return $this->belongTo('SwiftSalesCommissionScheme','scheme_id');
    }
    
    /*
     * Query
     */
    public static function getActiveBudgetBySalesman($salesman_id,$scheme_id,$date_start,$date_end)
    {
        return self::whereSalesmanId($salesman_id)
                ->whereSchemeId($scheme_id)
                ->where('date_start','>=',$date_start,'AND')
                ->where('date_end','<=',$date_end,'AND')
                ->first();
    }
}
