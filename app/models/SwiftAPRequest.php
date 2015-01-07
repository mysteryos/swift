<?php
/*
 * Name: Swift A&P Request
 * Description:
 */

class SwiftAPRequest extends Eloquent {
    use Illuminate\Database\Eloquent\SoftDeletingTrait;
    use \Venturecraft\Revisionable\RevisionableTrait; 
    
    public $readableName = "A&P Request";
    
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
    
    public $revisionClassName = "A&P Request";
    public $revisionPrimaryIdentifier = "name";
    public $keepCreateRevision = true;
    public $softDelete = true;
    
    /*
     * Elastic Search Indexing
     */
    
    //Indexing Enabled
    public $esEnabled = true;
    //Context for Indexing
    public $esContext = "aprequest";
    //Main Document
    public $esMain = true;
    
    /*
     * ElasticSearch Utility Id
     */
    
    public function esGetId()
    {
        return $this->id;
    }    
    
    public function getClassName()
    {
        return $this->revisionClassName;
    }
    
    public function getReadableName($html = false)
    {
        return $this->name." (Id:".$this->id.")";
    }
    
    public function getIcon()
    {
        return "fa-gift";
    }    
    
    /*
     * Relationships
     */
    
    public function customer()
    {
        return $this->belongsTo('JdeCustomer','customer_code','AN8');
    }
    
    public function product()
    {
        return $this->hasMany('SwiftAPProduct','aprequest_id');
    }
    
    public function requester()
    {
        return $this->belongsTo('users','requester_user_id');
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
    
    public function recent()
    {
        return $this->morphMany('SwiftRecent','recentable');
    }
    
    public function notification()
    {
        return $this->morphMany('SwiftNotification','notifiable');
    }
    
    public function story()
    {
        return $this->morphMany('SwiftStory','storyfiable');
    }
    
    /*
     * Helper Function
     */
    
    public static function getById($id)
    {
        return self::with('customer','product','product.jdeproduct','product.approval','product.approvalcatman','product.approvalexec','delivery','approval','order','document')->find($id);
    }
    
    /*
     * Utility
     */
    
    public function channelName()
    {
        return "apr_".$this->id;
    }

}
