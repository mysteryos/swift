<?php
/*
 * Name: Swift A&P Request
 * Description:
 */

class SwiftAPRequest extends Eloquent {
    use Illuminate\Database\Eloquent\SoftDeletingTrait;
    use \Venturecraft\Revisionable\RevisionableTrait; 
    
    protected $table = "swift_ap_request";
    
    protected $fillable = array("requester_user_id","customer_code","name","description","feedback_star","feedback_text");
    
    protected $guarded = array('id');
    
    public $timestamps = true;
    
    protected $dates = ['deleted_at'];
    
    /* Revisionable */
    
    protected $revisionEnabled = true;
    
    protected $keepRevisionOf = array(
        'customer_code','name','description','feedback_star', 'feedback_text'
    );
    
    protected $revisionFormattedFieldNames = array(
        'customer_code' => 'Customer Code',
        'name' => 'Name',
        'description' => 'Description',
        'feedback_star' => 'Feedback Star',
        'feedback_text' => 'Feedback Text',
    );    
    
    protected $revisionClassName = "A&P Request";
    protected $revisionPrimaryIdentifier = "name";
    protected $keepCreateRevision = true;
    protected $softDelete = true;
    /*
     * Relationships
     */
    
    public function customer()
    {
        return $this->belongsTo('JdeCustomer','customer_code','AN8');
    }
    
    public function product()
    {
        return $this->hasMany('SwiftApProduct','aprequest_id');
    }
    
    /*
     * Morphic
     */
    
    public function comments()
    {
        return $this->morphMany('SwiftComment', 'commentable');
    }
    
    public function workflow()
    {
        return $this->morphOne('SwiftWorkflowActivity', 'workflowable');
    }
    
    public function order()
    {
        return $this->morphMany('SwiftErpOrder','orderable');
    }
    
    public function document()
    {
        return $this->morphMany('SwiftDocument','document');
    }
    
    public function flag()
    {
        return $this->morphMany('SwiftFlag','flaggable');
    }
    
    public function approval()
    {
        return $this->morphMany('SwiftApproval','approvable');
    }
    
    public function delivery()
    {
        return $this->morphMany('SwiftDelivery','deliverable');
    }
    
    /*
     * Helper Function
     */
    
    public static function getById($id)
    {
        return self::with('customer','product','product.jdeproduct','product.approval','product.approvalcatman','product.approvalexec','delivery','approval','order','document')->find($id);
    }

}
