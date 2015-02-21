<?php
/**
 * Description of SwiftSalesCommissionScheme
 *
 * @author kpudaruth
 */
class SwiftSalesCommissionSchemeProductCategory extends Eloquent {

    use Illuminate\Database\Eloquent\SoftDeletingTrait;
    use \Venturecraft\Revisionable\RevisionableTrait;
    
    protected $table = "swift_com_scheme_product_category";
    
    protected $fillable = ['category'];
    
    protected $dates = ['deleted_at'];
    
    /* Revisionable */
    
    protected $revisionEnabled = true;
    protected $keepRevisionOf = array(
        'category'
    );
    
    protected $revisionFormattedFieldNames = array(
        'category' => 'Category',
    );
    
    public $revisionClassName = "Scheme Product Category";
    public $revisionPrimaryIdentifier = "id";
    public $keepCreateRevision = true;
    public $softDelete = true;
    
    public static $category = [
                        'KAN' => 'Kanasuk',
                        'WIN' => 'Wines',
                        'SPI' => 'Spirits',
                        'HPC' => 'Household',
                        'FOD' => 'Food',
                        'DRK' => 'Drinks',
                        'CIG' => 'Cigarettes',
                        'NES' => 'Nespresso',
                        'BEE' => 'Beer'
                    ];
    
    public static function boot() {
        parent:: boot();
        
        static::bootRevisionable();
    }
    
    /*
     * Accessors
     */
    
    public function getCategoryRevisionAttribute($val)
    {
        if(key_exists($val,self::$category))
        {
            return self::$category[$val];
        }
        else
        {
            return "";
        }        
    }
    
    /*
     * Relationships
     */
    
    public function rate()
    {
        return $this->hasManyThrough('SwiftSalesCommissionSchemeRate','SwiftSalesCommissionScheme','scheme_id','scheme_id');
    }
    
    public function category()
    {
        return $this->belongsTo('SwiftSalesCommissionScheme','scheme_id');
    }    
    
}
