<?php

class SwiftPRReason extends Eloquent {
    
    protected $table = "swift_pr_reason";
    
    protected $timestamp = false;
    
    /*
     * Relationships
     */
    
    public function product()
    {
        return $this->hasMany('SwiftPRPRoduct','reason_id');
    }
    
    /*
     * Queries
     */
    
    public function getAll()
    {
        $reasonArray = array();
        $list = self::all();
        foreach($list as $row)
        {
            $reasonArray[$row->id] = $row->name." (At ".$row->category.")";
        }
        
        return $reasonArray;
    }
    
}