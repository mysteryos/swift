<?php
/**
 * Description of SwiftPRDiscrepancy
 *
 * @author kpudaruth
 */

class SwiftPRDiscrepancy extends Eloquent
{
    use \Illuminate\Database\Eloquent\SoftDeletingTrait;
    
    protected $table = "swift_pr_discrepancy";
    
    protected $fillable = ['type','user_id','reason','product_id'];
    
    protected $dates = ['deleted_at'];

    /* Revisionable */

    protected $revisionEnabled = true;

    protected $keepRevisionOf = array(
        'reason'
    );

    protected $revisionFormattedFieldNames = array(
        'reason' => 'Discrepancy Reason'
    );

    public static $revisionName = "Product Discrepancy";

    public $revisionClassName = "Product Discrepancy";
    public $revisionPrimaryIdentifier = "product_id";
    public $keepCreateRevision = true;
    public $softDelete = true;
    public $revisionDisplayId = true;

    /*
     * Accessors
     */
    
    /*
     * Scope
     */
    
    /*
     * Relationships
     */

    public function product()
    {
        return $this->belongsTo('SWiftPRProdcut','product_id');
    }
    
    /*
     * Query
     */
    
}
