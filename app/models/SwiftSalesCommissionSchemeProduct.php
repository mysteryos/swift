<?php
/**
 * Description of SwiftSalesCommisionProduct
 *
 * @author kpudaruth
 */
class SwiftSalesCommissionSchemeProduct extends Eloquent {

    use Illuminate\Database\Eloquent\SoftDeletingTrait;
    use \Venturecraft\Revisionable\RevisionableTrait;
    
    protected $table = "swift_com_scheme_product";
    
    protected $fillable = ['jde_itm','scheme_id'];
    
    protected $dates = ['deleted_at'];
    
    protected $appends = ['name'];
    
    /* Revisionable */
    
    protected $revisionEnabled = true;
    protected $keepRevisionOf = array(
        'jde_itm'
    );
    
    protected $revisionFormattedFieldNames = array(
        'scheme_id' => 'Scheme',
        'name'  =>  'Name',
        'jde_itm'   =>  'Product'
    );
    
    public $revisionClassName = "Product";
    public $revisionPrimaryIdentifier = "name";
    public $keepCreateRevision = true;
    public $softDelete = true;
    
    
    public static function boot() {
        parent:: boot();
        
        static::bootRevisionable();
    }
    
    /*
     * Accessors
     */
    public function getCategoryIdRevisionAttribute($val)
    {
        $cat = SwiftSalesCommissionScheme::withTrashed()->find($val);
        return $cat->name;
    }
    
    public function getNameAttribute()
    {
        return trim($this->jdeproduct->DSC1)." - ".trim($this->jdeproduct->AITM);
    }
    
    public function getJdeItmRevisionAttribute($val)
    {
        $prod = \JdeProduct::where('ITM','=',$val)->first();
        return trim($prod->DSC1)." - ".trim($prod->AITM);
    }
    
    /*
     * Relationships
     */
    public function jdeproduct()
    {
        return $this->belongsTo('JdeProduct','jde_itm','ITM');
    }
    
    public function category()
    {
        return $this->belongsTo('SwiftSalesCommissionScheme','scheme_id');
    }
    
    public function rate()
    {
        return $this->hasManyThrough('SwiftSalesCommissionSchemeRate','SwiftSalesCommissionScheme','scheme_id','scheme_id');
    }
    
}
