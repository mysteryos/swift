<?php
/**
 * Description of SwiftACPCreditNote
 *
 * @author kpudaruth
 */

class SwiftACPCreditNote extends Eloquent
{
    use \Illuminate\Database\Eloquent\SoftDeletingTrait;
    use \Venturecraft\Revisionable\RevisionableTrait;
    use \Swift\ElasticSearchEventTrait;
    
    protected $table = "swift_acp_credit_note";
    
    protected $fillable = ['number'];
    
    protected $dates = ['deleted_at'];
    
    /* Revisionable */
    
    protected $revisionEnabled = true;
    
    protected $keepRevisionOf = array('number');
    
    protected $revisionFormattedFieldNames = array(
        'number' => 'Credit Note Number');
    
    public $saveCreateRevision = true;
    public $softDelete = true;
    public $revisionClassName =  "Accounts Payable Credit Note";
    public $revisionPrimaryIdentifier = "number";
    
    /* Elastic Search */
    
    //Indexing Enabled
    public $esEnabled = true;
    //Context for Indexing
    public $esContext = "acpayable";
    //Info Context
    public $esInfoContext = "creditnote";
    public $esRemove = ['ac_id'];

    public function esGetParent()
    {
        return $this->ac;
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

    public function ac()
    {
        return $this->belongsTo('SwiftACPRequest','ac_id');
    }
    
    /*
     * Query
     */
    
}
