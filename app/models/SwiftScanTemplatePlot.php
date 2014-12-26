<?php

class SwiftScanTemplatePlot extends Eloquent {
    protected $table = 'swift_scan_template_plot';
    
    protected $fields = array('template_name','name','type','options','coord_top','coord_left','coord_right','coord_bottom');
    
    protected $guarded = array('id');
    
    /*
     * Type
     */
    
    public static $typeText = 1;
    public static $typeBarcode = 2;
    public static $typeCheckmark = 3;
    
    /*
     * Accessors/Mutators
     */
    
    public function setOptionsAttribute($value)
    {
        $this->attributes['options'] = json_encode($value);
    }

    public function getOptionsAttribute($value)
    {
        return json_decode($value);
    }
    
    /*
     * Relationships
     */
    
    public function template()
    {
        return $this->hasOne('SwiftScanTemplate','template_name','name');
    }
    
    public function mapping()
    {
        return $this->hasOne('SwiftScanTemplateMapping','name','plot_name');
    }
}