<?php
/**
 * Description of SwiftCreditnote
 *
 * @author kpudaruth
 */

class SwiftCreditNote extends Eloquent
{
    use \Illuminate\Database\Eloquent\SoftDeletingTrait;
    use \Venturecraft\Revisionable\RevisionableTrait;
    use \Swift\ElasticSearchEventTrait;
    
    protected $table = "swift_credit_note";
    
    protected $fillable = ['number'];
    
    protected $dates = ['deleted_at'];
    
    /* Revisionable */
    
    protected $revisionEnabled = true;
    
    protected $keepRevisionOf = array('number');
    
    protected $revisionFormattedFieldNames = array(
        'number' => 'Credit Note Number'
    );
    
    public $saveCreateRevision = true;
    public $softDelete = true;
    public $revisionClassName =  "Credit Note";
    public $revisionPrimaryIdentifier = "number";
    
    /* Elastic Search */
    
    //Indexing Enabled
    public $esEnabled = true;
    //Info Context
    public $esInfoContext = "credit-note";
    public $esRemove = ['creditable_type','creditable_id'];

    public function esGetContext() {
        return array_search($this->creditable_type,\Config::get('context'));
    }

    public function esGetParent()
    {
        return $this->creditable;
    }
    
    /*
     * Event Observers
     */
    
    public static function boot() {
        parent:: boot();
        
        static::bootElasticSearchEvent();
        
        static::bootRevisionable();
    }    
    
    /*
     * Accessors
     */
    
    /*
     * Scope
     */
    
    /*
     * Relationships
     */

    public function creditable()
    {
        return $this->morphTo();
    }
    
    /*
     * Query
     */
    
}
