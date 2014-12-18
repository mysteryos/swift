<?php

class SwiftOcrTask extends Eloquent {
    
    use Illuminate\Database\Eloquent\SoftDeletingTrait;
    use \Venturecraft\Revisionable\RevisionableTrait;
    
    protected $table = "ocr_task";
    
    protected $guarded = array('id');
    
    protected $fillable = array('status','ocrtask_id','queued_at','inprogress_at','completed_at','retrieved_at');
    
    public $dates = ['queued_at','inprogress_at','completed_at','retrieved_at','deleted_at'];
    
    public static $INPROGRESS = 0;
    public static $COMPLETE = 1;
    public static $CANCELLED = -1;
    
    /*
     * relaltionship
     */
    
    public function scanDocument()
    {
        return $this->belongsTo('SwiftScanDocument','scan_doc_id');
    }
}
