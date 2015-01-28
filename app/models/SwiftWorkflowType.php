<?php

/* 
 * Name: Swift Workflow Type
 */

Class SwiftWorkflowType extends Eloquent 
{    
    
    /*
     * List of Workflow Types:
     * @order_tracking
     * @aprequest
     * @ocr_textfield
     */
    
    protected $table = 'swift_workflow_type';
    
    protected $guarded = array('id');
    
    protected $fillable = array('name','description','relation_type_id','data');
    
    public $timestamps = true;
    
    /*
     * Getter/Setter Methods for Data Field -- START
     */
    
    public function getDataAttribute($value)
    {
        return ($value == '' ? '' : json_decode($value));
    }
    
    public function setDataAttribute($value)
    {
        return ($value == '' ? '' : json_encode((array)$value));
    }
    
    /*
     * Getter/Setter Methods for Data Field -- END
     */
    
    /*
     * Relationship - Node
     */
    
    public function nodes()
    {
        return $this->hasMany('SwiftNodeType','workflow_type_id');
    }
    
    public function activity()
    {
        return $this->hasOne('SwiftWorkflowActivity','workflow_type_id');
    }
    
    /*
     * Query Functions
     */
    
    public static function getByName($name)
    {
        return self::where('name','=',$name)->first();
    }
    
}
