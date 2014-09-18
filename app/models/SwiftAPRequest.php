<?php
/*
 * Name: Swift A&P Request
 * Description:
 */

class SwiftAPRequest extends eloquent {
    use Illuminate\Database\Eloquent\SoftDeletingTrait;
    use \Venturecraft\Revisionable\RevisionableTrait; 
    
    protected $table = "swift_ap_request";
    
    protected $fillable = array("customer_code","name","description","feedback_star","feedback_text");
    
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
    
    protected $revisionClassName = "A&P Product";
    
    protected $saveCreateRevision = true;
    
    /*
     * Relationships
     */
    
    public function customer()
    {
        return $this->belongTo('JdeCustomer','customer_code','an8');
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
    /*
     * Morphic
     */
    public function document()
    {
        return $this->morphMany('SwiftDocument','document');
    }
    
    public function flag()
    {
        return $this->morphMany('SwiftFlag','flaggable');
    }

}
