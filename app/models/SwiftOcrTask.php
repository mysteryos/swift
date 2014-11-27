<?php

class SwiftOcrTask extends Eloquent {
    
    use Illuminate\Database\Eloquent\SoftDeletingTrait;
    use \Venturecraft\Revisionable\RevisionableTrait;
    
    protected $table = "abbycloudocr_task";
    
    protected $guarded = array('id');
    
    protected $fillable = array('status','queued_at','inprogress_at','completed_at');
    
    public $dates = ['queued_at','inprogress_at','completed_at','deleted_at'];
    
    /*
     * polymorphic
     */
    
    public function taskable()
    {
        return $this->morphTo();
    }
    
    public function scanInvoice()
    {
        return $this->morphMany('');
    }
}
