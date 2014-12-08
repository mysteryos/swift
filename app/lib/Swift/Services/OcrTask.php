<?php
/*
 * Name:
 * Description:
 */

Namespace Swift\Services;

Use Helper;
Use Log;

class OcrTask {
    /*
     * Create task
     */
    
    public function __construct(){
        if(!Helper::loginSysUser())
        {
            Log::error('Unable to login system user');
        }
    }
    
    public function init($obj,$scanModel)
    {
        
    }
    
    
    
}