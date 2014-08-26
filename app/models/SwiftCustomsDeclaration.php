<?php
/*
 * Name: Swift Customs Declaration
 * Description: All declared documents to customs goes here
 */

class SwiftCustomsDeclaration extends Eloquent {
    use Illuminate\Database\Eloquent\SoftDeletingTrait;    
    use \Venturecraft\Revisionable\RevisionableTrait;
    
    /*
     * Eloquent Attributes
     */
    
    protected $table = "swift_customs_declaration";
    
    protected $guarded = array('id');
    
    protected $fillable = array('order_id','customs_reference','customs_processed_at','customs_filled_at','customs_status');
    
    public $timestamps = true;
    
    protected $touches = array('order');
    
    public $dates = ['customs_processed_at','customs_filled_at','deleted_at'];
    
    /* Revisionable Attributes */
    
    protected $revisionEnabled = true;
    
    protected $keepRevisionOf = array(
        'customs_reference','customs_processed_at','customs_filled_at', 'customs_status'
    );
    
    protected $revisionFormattedFieldNames = array(
        'customs_reference'=>'Bill of entry number',
        'customs_processed_at'=>'Customs processed at',
        'customs_filled_at'=>'Customs filled at',
        'customs_status'=>'Customs status'
    );
    
    protected $keepCreateRevision = true;
    
    const FILLED = 1;
    const PROCESSING = 2;
    const CLEARED = 3;
    
    /*
     * Public Attributes
     */
    public static $status = array(self::FILLED=>'Filled on system',
                                    self::PROCESSING=>'Processing',
                                    self::CLEARED=>'Cleared');
    
    /*
     * Functions
     */
    public function order()
    {
        return $this->belongsTo('SwiftOrder','order_id');
    }
}
