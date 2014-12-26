<?php
/**
 * Description of SwiftScanTemplateMapping
 *
 * @author kpudaruth
 */
class SwiftScanTemplateMapping extends Eloquent {

    protected $table = "swift_scan_template_mapping";
    
    protected $fields = array('plot_name','field_name');
    
    public function plot()
    {
        return $this->hasOne('SwiftScanTemplatePlot','plot_name','name');
    }

}
