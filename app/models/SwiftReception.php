<?php
/**
 * Name: SwiftReception
 *
 * @author kpudaruth
 */

class SwiftReception extends Eloquent {
    
    use Illuminate\Database\Eloquent\SoftDeletingTrait;
    use \Venturecraft\Revisionable\RevisionableTrait;
    
    protected $table = "swift_reception";
    
    protected $guarded = "id";
    
    protected $fillable = array('order_id','reception_date','grn','reception_user');
    
    public $timestamps = true;
    
    protected $touches = array('order');
    
    protected $dates = ['deleted_at','reception_date'];
    
    /* Revisionable Attributes */
    
    protected $revisionEnabled = true;
    
    protected $keepRevisionOf = array(
        'reception_date','grn','reception_user'
    );
    
    protected $revisionFormattedFieldNames = array(
        'reception_date' => 'Reception Date',
        'grn' => 'GRN number',
        'reception_user' => 'Received By'
    );
    
    protected $keepCreateRevision = true;     
    
    /*
     * Mutator
     */
    
    public function setReceptionDateAttribute($value)
    {
        //Add missing seconds value
        $this->attributes['reception_date'] = ($value != "" ? Carbon::parse($value)->toDateTimeString(): "");
    }
    
    /*
     * Revision Accessor
     */
    
    public function getReceptionUserRevisionAttribute($value)
    {
        if($value != "")
        {
            $user = Sentry::findUserById($value);
            if($user)
            {
                return $user->first_name." ".$user->last_name;
            }
            else
            {
                return "(Unknown)";
            }
        }
        return "";
    }
    
    /*
     * Relationships
     */
    public function order()
    {
        return $this->belongsTo('SwiftOrder','order_id');
    }
    
}
