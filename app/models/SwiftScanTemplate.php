<?php

class SwiftScanTemplate extends Eloquent {
    protected $table = 'swift_scan_template';
    
    protected $fields = array('name','type');
    
    protected $guarded = array('id');
    
    /*
     * Types of Scan Template
     */
    
    public static $textField = 1;
    public static $image = 2;
    public static $businessCard = 3;
    
    public function plot()
    {
        return $this->hasMany('SwiftScanTemplatePlot','template_name','name');
    }
}