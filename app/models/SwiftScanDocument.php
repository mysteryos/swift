<?php

class SwiftScanDocument extends Eloquent {
    use Illuminate\Database\Eloquent\SoftDeletingTrait;    
    
    protected $table = "swift_scan_document";
    
    protected $fields = array('invoice_id','category','s3_key','s3_lastmodified');
    
    protected $guarded = array('id');
    
    public $timestamps = true;
    
    protected $dates = ['s3_lastmodified','deleted_at'];
    
    public static function getS3KeyCount($s3_key)
    {
        return self::where('s3_key','=',$s3_key)->count();
    }
    
    public function invoice()
    {
        return $this->belongsTo('SwiftScanInvoice','invoice_id');
    }
    
    public function ocrTask()
    {
        return $this->hasMany('SwiftOcrTask','scan_doc_id');
    }
    
    public function scannable()
    {
        return $this->morphTo();
    }
    
    public function template()
    {
        return $this->hasOne('SwiftScanTemplate','name','scan_template');
    }
    
    public function plot()
    {
        return $this->hasMany('SwiftScanTemplatePlot','template_name','scan_template');
    }
    
    
}