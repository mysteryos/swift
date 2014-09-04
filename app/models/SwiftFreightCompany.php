<?php
/*
 * Name: Swift Freight Company
 * Description:
 */

class SwiftFreightCompany extends Eloquent {
    
    use Illuminate\Database\Eloquent\SoftDeletingTrait;
    use \Venturecraft\Revisionable\RevisionableTrait;
    
    protected $table = "swift_freight_company";
    
    protected $guarded = array('id');
    
    protected $fillable = array('name','address','tel','fax','email','brn','vat_no','data','type');
    
    public $timestamps = true;
    
    /* Revisionable Attributes */
    
    protected $revisionEnabled = true;
    
    protected $keepRevisionOf = array(
        'name','address','tel', 'fax', 'email', 'brn', 'vat_no', 'type'
    );
    
    protected $revisionFormattedFieldNames = array(
        'vat_no' => 'VAT number',
        'brn'   => 'BRN'
    );
    
//    protected $revisionFormattedFieldNames = array(
//        'freight_type' => 'Freight Type',
//        'bol_no' => 'Bill of Lading Number',
//        'vessel_no' => 'Vessel Number',
//        'incoterms' => 'Incoterms',
//        'freight_etd' => 'Freight ETD',
//        'freight_eta' => 'Freight ETA',
//    );
    
    /*
     * General Attributes
     */
    
    const LOCAL = 1;
    const FOREIGN = 2;
    const INTERNATIONAL = 3;
    
    public static $type = array(self::LOCAL=>'Local',
                                    self::FOREIGN=>'Foreign',
                                    self::INTERNATIONAL=>'International');    
    
    /*
     * Getter/Setter Methods for Data Field -- START
     */
    
    public function getDataAttribute($value)
    {
        return ($value == '' ? '' : json_decode($value));
    }
    
    public function setDataAttribute($value)
    {
        return ($value == '' ? '' : json_encode((array)$value));
    }
    
    /*
     * Getter/Setter Methods for Data Field -- END
     */    
    
    
    /*
     * Relationships
     */
    public function freight()
    {
        return $this->hasMany('SwiftFreight','freight_company_id');
    }
    
    /*
     * Helper Functions
     */
    
    public static function getById($id)
    {
        return self::with('freight')->find($id);
    }
    
    public static function getByName($term,$offset,$limit)
    {
        return self::where('name','LIKE',"%$term%")->limit($limit)->offset($offset)->get();
    }
    
}